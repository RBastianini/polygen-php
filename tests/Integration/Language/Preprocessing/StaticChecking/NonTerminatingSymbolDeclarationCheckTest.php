<?php

namespace Tests\Integration\Language\Preprocessing\StaticChecking;

use Polygen\Language\Errors\ErrorCollection;
use Polygen\Language\Errors\UndeclaredNonTerminatingSymbol;
use Polygen\Language\Preprocessing\StaticCheck\NonTerminatingSymbolDeclarationCheck;
use Polygen\Language\Token\Token;
use Tests\DocumentUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class NonTerminatingSymbolDeclarationCheckTest extends TestCase
{
    use DocumentUtils;
    use StaticCheckUtils;
    use StreamUtils;

    public function provider_undefined_non_terminating_symbol()
    {
        return [
            [
                <<<GRAMMAR
                S ::= test1, test2 and 3, test4, Missing, test6;
GRAMMAR
            ],
            [
                <<<GRAMMAR
                S ::= test1, test2 A 3, test4, Missing, test6;
                (* Here the A symbol is defined in the global scope, so it should not be reported. *)
                A := defined;
GRAMMAR
            ],
            [
                <<<GRAMMAR
                S ::= test1, test2 A 3, test4, not missing, test6;
                A := (defined | Missing);
GRAMMAR
            ],
            [
                <<<GRAMMAR
                S ::= test1, test2 A 3, test4, not missing, test6;
                A := >>(defined | <Missing)<<;
GRAMMAR
            ],
            [
                <<<GRAMMAR
                (* Here the A symbol is defined in the local scope, so it should not be reported. *)
                S ::= (A:= defined; A Missing);
GRAMMAR
            ],
            [
                <<<GRAMMAR
                (* The Missing symbol is not defined for the current scope, so it should be reported. *)
                S ::= test1, test2 A 3, test4, Missing, test6;
                A := (Missing := missing; Missing);
GRAMMAR
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provider_undefined_non_terminating_symbol
     * @param string $grammar
     */
    public function it_reports_undefined_non_terminating_symbol($grammar)
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream($grammar)
        );

        $checker = $this->given_a_static_checker_with(new NonTerminatingSymbolDeclarationCheck());

        $result = $checker->check($document);

        $this->assertInstanceOf(ErrorCollection::class, $result);

        $expectedErrors = [new UndeclaredNonTerminatingSymbol(Token::nonTerminatingSymbol('Missing'))];

        $this->assertEquals($expectedErrors, $result->getErrors());
    }

    /**
     * @test
     */
    public function it_reports_no_error_if_there_isnt_any()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                        S ::= A | B (C ::= lol; C | D);
                        D ::= a;
                        A ::= B B B;
                        B ::= yes;
GRAMMAR

            )
        );

        $checker = $this->given_a_static_checker_with(new NonTerminatingSymbolDeclarationCheck());

        $result = $checker->check($document);

        $this->assertInstanceOf(ErrorCollection::class, $result);

        $this->assertEmpty($result->getErrors());
    }
}
;

