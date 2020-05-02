<?php

namespace Tests\Polygen\Integration\Language\Preprocessing\StaticChecking;

use Polygen\Language\Errors\NoStartSymbol;
use Polygen\Language\Preprocessing\StaticCheck\StartSymbolCheck;
use Tests\DocumentUtils;
use Tests\Integration\Language\Preprocessing\StaticChecking\StaticCheckUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class StartSymbolTest extends TestCase
{
    use DocumentUtils;
    use StaticCheckUtils;
    use StreamUtils;

    /**
     * @test
     */
    public function it_does_not_report_errors_if_there_isnt_any()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            S ::= a;
GRAMMAR
            )
        );

        $checker = $this->given_a_static_checker_with(new StartSymbolCheck());

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

        $checker = $checker = $this->given_a_static_checker_with(new StartSymbolCheck());

        $errors = $checker->check($document);

        $expectedErrors = [new NoStartSymbol()];

        $this->assertEquals($expectedErrors, $errors->getErrors());
    }
}
