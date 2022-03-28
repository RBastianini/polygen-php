<?php

namespace Tests\Polygen\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToProductionConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\UnfoldedSubproductionConverter;
use Tests\DocumentUtils;
use Tests\Integration\Language\Preprocessing\ConcreteToAbstractConversion\ConverterUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class UnfoldedSubproductionConverterTest extends TestCase
{
    use ConverterUtils;
    use DocumentUtils;
    use StreamUtils;

    /**
     * @test
     */
    public function it_converts_an_unfolded_subproduction_at_the_root_level()
    {

        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Source ::= a | b | c | d: e >(Z ::= x; l | m | n | o p).q r | s | t;
            Expected ::= (
                Z ::= x;
                a | b | c
                | d: e (l).q r
                | d: e (m).q r
                | d: e (n).q r
                | d: e (o p).q r
                | s | t
            );
GRAMMAR
            )
        );

        $converter = $this->given_a_converter_with(
            new AtomSequenceToProductionConverter(),
            new UnfoldedSubproductionConverter()
        );

        $convertedDocument = $converter->convert($document);

        $this->assertEquals(
            $convertedDocument->getDeclaration('Expected')->getProductionSet(),
            $convertedDocument->getDeclaration('Source')->getProductionSet()
        );
    }

    /**
     * @test
     */
    public function it_converts_an_unfolded_subproduction_at_a_nested_level()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Source ::= a | b | ( c | d: e >(Z ::= x; l | m | n | o p).q r | s ) t;
            Expected ::=
                a | b
                | (
                    (
                        Z ::= x;
                        c
                        | d: e (l).q r
                        | d: e (m).q r
                        | d: e (n).q r
                        | d: e (o p).q r
                        | s
                    )
                )
                t;
GRAMMAR
            )
        );

        $converter = $this->given_a_converter_with(
            new AtomSequenceToProductionConverter(),
            new UnfoldedSubproductionConverter()
        );

        $convertedDocument = $converter->convert($document);

        $this->assertEquals(
            $convertedDocument->getDeclaration('Expected')->getProductionSet(),
            $convertedDocument->getDeclaration('Source')->getProductionSet()
        );
    }

    /**
     * @test
     */
    public function it_converts_multiple_unfolded_subproductions()
    {

        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Source ::= a | b | c: d >(Z ::= x; e | l ).m n | o | p: > (Y ::= w; q | r ).s t | u;
            Expected ::= (
                Z ::= x;
                (
                    Y ::= w;
                    a | b
                    | c: d (e).m n
                    | c: d (l).m n
                    | o
                    | p: (q).s t
                    | p: (r).s t
                    | u
                )
            );
GRAMMAR
            )
        );

        $converter = $this->given_a_converter_with(
            new AtomSequenceToProductionConverter(),
            new UnfoldedSubproductionConverter()
        );

        $convertedDocument = $converter->convert($document);

        $this->assertEquals(
            $convertedDocument->getDeclaration('Expected')->getProductionSet(),
            $convertedDocument->getDeclaration('Source')->getProductionSet()
        );
    }

}
