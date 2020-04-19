<?php

namespace Tests\Polygen\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToLabelableConverter;
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
            Source ::= test1, test2 and 3, test4, test5, test6;
            Expected ::= (test1 | test2) and (3 | test4 | test5 | test6);
GRAMMAR
            )
        );

        $converter = $this->given_a_converter_with(new AtomSequenceToLabelableConverter());

        $convertedDocument = $converter->convert($document);

        $this->assertEquals(
            $convertedDocument->getDefinition('Expected')->getProductions(),
            $convertedDocument->getDefinition('Source')->getProductions()
        );
    }
}
