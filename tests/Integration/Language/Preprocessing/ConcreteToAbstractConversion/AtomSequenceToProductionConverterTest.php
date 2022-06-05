<?php

namespace Tests\Polygen\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToProductionConverter;
use Tests\DocumentUtils;
use Tests\Integration\Language\Preprocessing\ConcreteToAbstractConversion\ConverterUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class AtomSequenceToProductionConverterTest extends TestCase
{
    use ConverterUtils;
    use DocumentUtils;
    use StreamUtils;

    /**
     * @test
     */
    public function it_converts_atom_sequences_to_productions()
    {

        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Source ::= reworked test1, test2 and test3, test4 end;
            Expected ::= reworked (test1 and test3 | test2 and test4) end;
GRAMMAR
            )
        );

        $converter = $this->given_a_converter_with(new AtomSequenceToProductionConverter());

        $convertedDocument = $converter->convert($document);

        $this->assertEquals(
            $convertedDocument->getDeclaration('Expected')->getProductionSet(),
            $convertedDocument->getDeclaration('Source')->getProductionSet()
        );
    }

    /**
     * @test
     */
    public function it_does_not_contain_off_by_one_errors()
    {

        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Source ::= test1, test2 and test3, test4;
            Expected ::= (test1 and test3 | test2 and test4);
GRAMMAR
            )
        );

        $converter = $this->given_a_converter_with(new AtomSequenceToProductionConverter());

        $convertedDocument = $converter->convert($document);

        $this->assertEquals(
            $convertedDocument->getDeclaration('Expected')->getProductionSet(),
            $convertedDocument->getDeclaration('Source')->getProductionSet()
        );
    }
}
