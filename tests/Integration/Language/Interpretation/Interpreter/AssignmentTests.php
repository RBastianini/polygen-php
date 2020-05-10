<?php

namespace Tests\Integration\Language\Interpretation\Interpreter;

use Polygen\Language\Document;
use Polygen\Language\Interpretation\Context;
use Polygen\Polygen;
use Tests\StreamUtils;
use Tests\TestCase;

class AssignmentTests extends TestCase
{
    use StreamUtils;

    /**
     * @test
     */
    public function it_does_not_evaluate_assignments_again_once_defined()
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_stream(
                <<<GRAMMAR
            S ::= \A is just A and nothing more;
            A := life | work;
GRAMMAR
            ),
            Document::START
        );
        $generated1 = $polygen->generate($document, new Context(Document::START, 1));

        $this->assertEquals('Life is just life and nothing more', $generated1);

        $generated2 = $polygen->generate($document, new Context(Document::START, 2));
        $this->assertEquals('Work is just work and nothing more', $generated2);
    }

    /**
     * @test
     */
    public function it_does_not_evaluate_assignments_again_even_if_referenced_from_a_different_symbol()
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_stream(
                <<<GRAMMAR
            S ::= Ref1 Ref2;
            Ref1 ::= >>(A)<<;
            Ref2 ::= A;
            A := a|b;
GRAMMAR
            ),
            Document::START
        );
        $generated = $polygen->generate($document, $context = new Context(Document::START, 933208062));

        $acceptable = [
            'a a',
            'b b',
        ];

        $this->assertContains($generated, $acceptable);
    }

    /**
     * @test
     */
    public function it_does_evaluate_assignments_in_different_scopes()
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_stream(
                <<<GRAMMAR
            S ::= A (A := b; A);
            A := a;
GRAMMAR
            ),
            Document::START
        );
        $generated = $polygen->generate($document, $context = new Context(Document::START));

        $this->assertEquals('a b', $generated);
    }
}
