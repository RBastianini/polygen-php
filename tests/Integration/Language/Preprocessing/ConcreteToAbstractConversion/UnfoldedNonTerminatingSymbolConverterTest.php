<?php

namespace Tests\Polygen\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToProductionConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\UnfoldedNonTerminatingSymbolConverter;
use Tests\DocumentUtils;
use Tests\Integration\Language\Preprocessing\ConcreteToAbstractConversion\ConverterUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class UnfoldedNonTerminatingSymbolConverterTest extends TestCase
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
            Source ::= a | b | c | d: e >F.g h | i | j;
            F ::= f1 | f2 | f3 | (X ::= x; f4 X f5).p;
            Expected ::= (
                a | b | c
                | d: e (f1).g h
                | d: e (f2).g h
                | d: e (f3).g h
                | d: e ((X ::= x; f4 X f5).p).g h
                | i | j
            );
GRAMMAR
            )
        );

        $converter = $this->given_a_converter_with(
            new AtomSequenceToProductionConverter(),
            new UnfoldedNonTerminatingSymbolConverter()
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
            Source ::= ( a | (B ::= e1 | e2 | e3; c | d: e >B.f g | h) i | j ) k;
            Expected ::= (
                a
                | (
                        B ::= e1 | e2 | e3;
                    (
                        c
                        | d: e (e1).f g
                        | d: e (e2).f g
                        | d: e (e3).f g
                        | h
                    )
                ) i | j
            ) k;
GRAMMAR
            )
        );

        $converter = $this->given_a_converter_with(
            new AtomSequenceToProductionConverter(),
            new UnfoldedNonTerminatingSymbolConverter()
        );

        $convertedDocument = $converter->convert($document);

        $this->assertEquals(
            $convertedDocument->getDeclaration('Expected')->getProductionSet(),
            $convertedDocument->getDeclaration('Source')->getProductionSet()
        );
    }
}
