<?php

namespace Tests\Polygen\Integration\Language\StaticChecking;

use Polygen\Language\Errors\NoStartSymbol;
use Polygen\Language\Errors\UndefinedNonTerminatingSymbol;
use Polygen\Language\StaticChecking\StaticChecker;
use Polygen\Language\Token\Token;
use Tests\DocumentUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class StatickCheckerTest extends TestCase
{
    use DocumentUtils;
    use StreamUtils;

    /**
     * @test
     */
    public function it_does_not_report_errors_if_there_isnt_any()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            S ::= a [very] simple grammar | a not so complex grammar;
GRAMMAR
            )
        );

        $checker = new StaticChecker();

        $errors = $checker->check($document);

        $this->assertEmpty($errors->getErrors());
    }

    /**
     * @test
     */
    public function it_reports_missing_start_symbol()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Source ::= test1, test2 and 3, test4, test5, test6;
            Expected ::= (test1 | test2) and (3 | test4 | test5 | test6);
GRAMMAR
            )
        );

        $checker = new StaticChecker();

        $errors = $checker->check($document);

        $expectedErrors = [new NoStartSymbol()];

        $this->assertEquals($expectedErrors, $errors->getErrors());
    }


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

        $checker = new StaticChecker();

        $errors = $checker->check($document);

        $expectedErrors = [new UndefinedNonTerminatingSymbol(Token::nonTerminatingSymbol('Missing'))];

        $this->assertEquals($expectedErrors, $errors->getErrors());
    }

    /**
     * @test
     */
    public function it_reports_more_than_one_error()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Source ::= Missing;
GRAMMAR
            )
        );

        $checker = new StaticChecker();

        $errors = $checker->check($document);

        $this->assertCount(2, $errors->getErrors());

        $expectedErrors = [
            new NoStartSymbol(),
            new UndefinedNonTerminatingSymbol(Token::nonTerminatingSymbol('Missing')),
        ];

        $this->assertEquals($expectedErrors, $errors->getErrors());
    }
}
