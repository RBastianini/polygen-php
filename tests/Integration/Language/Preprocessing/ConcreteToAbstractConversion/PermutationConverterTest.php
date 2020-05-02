<?php

namespace Tests\Polygen\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToProductionConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\PermutationConverter;
use Tests\DocumentUtils;
use Tests\Integration\Language\Preprocessing\ConcreteToAbstractConversion\ConverterUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class PermutationConverterTest extends TestCase
{
    use ConverterUtils;
    use DocumentUtils;
    use StreamUtils;

    /**
     * @test
     */
    public function it_generates_all_permutations_1()
    {

        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Source ::= se {e'} {quindi} {egli};
            Expected ::= (
                      se (e')       (quindi)    (egli)
                    | se (e')       (egli)      (quindi)
                    | se (quindi)   (e')        (egli)
                    | se (quindi)   (egli)      (e')
                    | se (egli)     (e')        (quindi)
                    | se (egli)     (quindi)    (e')
                );
GRAMMAR
            )
        );

        $converter = $this->given_a_converter_with(
            new AtomSequenceToProductionConverter(),
            new PermutationConverter()
        );

        $convertedDocument = $converter->convert($document);

        $this->assertEquals(
            $convertedDocument->getDefinition('Expected')->getProductions(),
            $convertedDocument->getDefinition('Source')->getProductions()
        );
    }

    /**
     * @test
     */
    public function it_generates_all_permutations_2()
    {

        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Source ::= something | begin {A ::= a; A}.l1 mid { B ::= b; B }.l2 end { C::= c; C }.l3;
            Expected ::= something | (
                      begin (A ::= a; A).l1 mid (B ::= b; B).l2 end (C ::= c; C).l3
                    | begin (A ::= a; A).l1 mid (C ::= c; C).l3 end (B ::= b; B).l2
                    | begin (B ::= b; B).l2 mid (A ::= a; A).l1 end (C ::= c; C).l3
                    | begin (B ::= b; B).l2 mid (C ::= c; C).l3 end (A ::= a; A).l1
                    | begin (C ::= c; C).l3 mid (A ::= a; A).l1 end (B ::= b; B).l2
                    | begin (C ::= c; C).l3 mid (B ::= b; B).l2 end (A ::= a; A).l1
                );
GRAMMAR
            )
        );

        $converter = $this->given_a_converter_with(
            new AtomSequenceToProductionConverter(),
            new PermutationConverter()
        );

        $convertedDocument = $converter->convert($document);

        $this->assertEquals(
            $convertedDocument->getDefinition('Expected')->getProductions(),
            $convertedDocument->getDefinition('Source')->getProductions()
        );
    }
}
