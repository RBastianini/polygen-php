<?php

namespace Tests\Integration\Language\Interpretation;

use Polygen\Language\Document;
use Polygen\Language\Exceptions\StaticCheckException;
use Polygen\Language\Interpretation\Context;
use Polygen\Polygen;
use Tests\StreamUtils;
use Tests\TestCase;

class InterpreterTest extends TestCase
{
    use StreamUtils;

    /**
     * @test
     */
    public function it_does_not_eat_up_all_available_stack_space()
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_file(__DIR__ . '/../../../files/incredible-commit.grm'),
            Document::START
        );

        // Initialize a context with a seed known to make the interpreter use up all stack space and crash.
        $context = Context::get(Document::START, '188900443');

        $result = $polygen->generate($document, $context);

        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */

    public function it_supports_epsilon_productions()
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_stream('S ::= _ this _ is _ a _ weird _ looking _ grammar;')
        );

        $result = $polygen->generate($document);

        $this->assertEquals('this is a weird looking grammar', $result);
    }

    /**
     * @test
     */
    public function it_supports_empty_productions()
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_stream('S ::= _;')
        );

        $result = $polygen->generate($document);

        $this->assertEquals('', $result);
    }

    /**
     * @test
     */
    public function it_supports_capitalization()
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_stream('S ::= this is a \polygen grammar;')
        );

        $result = $polygen->generate($document);

        $this->assertEquals('this is a Polygen grammar', $result);
    }

    public function it_supports_string_concatenation()
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_stream('S ::= this is a standard "P" ^ olygen grammar;')
        );

        $result = $polygen->generate($document);

        $this->assertEquals('this is a standard Polygen grammar', $result);
    }

    /**
     * @test
     */
    public function it_capitalizes_only_the_first_terminating_symbol_it_finds()
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_stream('S ::= a ^_ a ^_\ a \^_a _^\a;')
        );

        $result = $polygen->generate($document);

        $this->assertEquals('aaAAA', $result);
    }

    /**
     * @test
     */
    public function it_throws_static_check_exceptions()
    {
        $polygen = new Polygen();

        $this->expectException(StaticCheckException::class);

        $polygen->getDocument(
            $this->given_a_source_stream('I := "where is the S symbol!?";'),
            Document::START
        );
    }
}
