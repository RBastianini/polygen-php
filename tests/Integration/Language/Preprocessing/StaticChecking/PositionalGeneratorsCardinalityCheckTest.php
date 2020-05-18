<?php

namespace Tests\Polygen\Integration\Language\Preprocessing\StaticChecking;

use Polygen\Grammar\Atom;
use Polygen\Grammar\AtomSequence;
use Polygen\Language\Errors\MismatchingPositionalGeneratorCardinality;
use Polygen\Language\Preprocessing\StaticCheck\PositionalGeneratorsCardinalityCheck;
use Polygen\Language\Token\Token;
use Tests\DocumentUtils;
use Tests\Integration\Language\Preprocessing\StaticChecking\StaticCheckUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class PositionalGeneratorsCardinalityCheckTest extends TestCase
{
    use DocumentUtils;
    use StaticCheckUtils;
    use StreamUtils;

    /**
     * @test
     */
    public function it_does_not_report_an_error_if_there_are_no_atom_sequences()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    S ::= here (There ::= are; a lot of things);
                    T ::= [but] {not} {sequences}; 
GRAMMAR
            )
        );

        $subject = $this->given_a_static_checker_with(new PositionalGeneratorsCardinalityCheck());

        $result = $subject->check($document);

        $this->assertTrue($result->isEmpty());
    }

    /**
     * @test
     */
    public function it_does_not_report_an_error_if_there_is_just_one_atom_sequence()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    S ::= here there,is,only,one sequence;
GRAMMAR
            )
        );

        $subject = $this->given_a_static_checker_with(new PositionalGeneratorsCardinalityCheck());

        $result = $subject->check($document);

        $this->assertTrue($result->isEmpty());
    }

    /**
     * @test
     */
    public function it_does_not_report_an_error_if_all_atom_sequences_have_the_same_cardinality()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    S ::= here there,are multiple,sequences but all,with just two,elemnts each;
GRAMMAR
            )
        );

        $subject = $this->given_a_static_checker_with(new PositionalGeneratorsCardinalityCheck());

        $result = $subject->check($document);

        $this->assertTrue($result->isEmpty());
    }

    /**
     * @test
     */
    public function it_does_report_an_error_if_not_all_atom_Sequences_have_the_same_cardinality()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    S ::= here there,are multiple,sequences but one,of,which with the,wrong number of,elements;
GRAMMAR
            )
        );

        $subject = $this->given_a_static_checker_with(new PositionalGeneratorsCardinalityCheck());

        $result = $subject->check($document);

        $expected = [
            new MismatchingPositionalGeneratorCardinality(
                new AtomSequence([
                    new Atom\SimpleAtom(Token::terminatingSymbol('one')),
                    new Atom\SimpleAtom(Token::terminatingSymbol('of')),
                    new Atom\SimpleAtom(Token::terminatingSymbol('which')),
                ]),
                2
            )
        ];

        $this->assertEquals($expected, $result->getErrors());
    }
}
