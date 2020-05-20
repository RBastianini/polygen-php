<?php

namespace Tests\Polygen\Integration\Language\Interpretation\Interpreter;

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
        $generated1 = $polygen->generate($document, Context::get(Document::START, 1));

        $this->assertEquals('Life is just life and nothing more', $generated1);

        $generated2 = $polygen->generate($document, Context::get(Document::START, 2));
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
        $generated = $polygen->generate($document, $context = Context::get(Document::START, 933208062));

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
        $generated = $polygen->generate($document, $context = Context::get(Document::START));

        $this->assertEquals('a b', $generated, $context->getSeed());
    }

    /**
     * This example comes straight from the documentation, section 2.0.11.3 "Scoping statico lessicale".
     *
     * @test
     * @param string $seed
     * @dataProvider provider_bunch_of_seeds
     */
    public function it_evaluates_assignments_each_in_their_respective_scope($seed)
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    S ::= (X ::= a | b; A);
                    A ::= X X ;
                    X := x | y ;
GRAMMAR
            )
        );

        $generated = $polygen->generate($document, $context = Context::get(Document::START, $seed));

        $expected = [
            'x x',
            'y y',
        ];

        $this->assertContains($generated, $expected, $seed);
    }

    public function provider_bunch_of_seeds()
    {
        return [
            ['1'],
            ['2'],
            ['3'],
            ['4'],
            ['5'],
            ['6'],
        ];
    }

    /**
     * This example comes straight from the documentation, section 2.0.11.3 "Scoping statico lessicale".
     * @test
     * @param string $seed
     * @dataProvider provider_bunch_of_seeds
     */
    public function it_allows_shadowing_a_declaration($seed)
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_stream(
                    'S ::= (X ::= a | b; (X ::= x | y; X)) ;'
            )
        );

        $generated = $polygen->generate($document, $context = Context::get(Document::START, $seed));

        $expected = [
            'x',
            'y',
        ];

        $this->assertContains($generated, $expected, $seed);
    }

    /**
     * This example comes straight from the documentation, section 2.0.11.3 "Scoping statico lessicale".
     * @test
     * @param string $seed
     * @dataProvider provider_bunch_of_seeds
     */
    public function it_disallows_using_a_shadowed_declaration($seed)
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_stream(
                'S ::= (X ::= a | b; (X ::= x [X]; X)) ;'
            )
        );

        $generated = $polygen->generate($document, $context = Context::get(Document::START, $seed));

        $this->assertRegExp('{x( x)*}', $generated, $seed);
    }

}
