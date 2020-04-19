<?php

namespace Tests\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Language\Preprocessing\AbstractToConcreteSyntaxConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToLabelableConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\DeepUnfoldingConverter;
use Tests\DocumentUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class DeepUnfoldingConverterTest extends TestCase
{
    use ConverterUtils;
    use DocumentUtils;
    use StreamUtils;

    /**
     * @var AbstractToConcreteSyntaxConverter
     */
    private $subject;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->given_a_converter_with(
            new AtomSequenceToLabelableConverter(),
            new DeepUnfoldingConverter()
        );
    }

    /**
     * @test
     */
    public function it_converts_deep_unfolding()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Original ::= > >> il (cane | gatto) | la < (pecora | capra) << | lo storione ;
            Expected ::= > (il > (cane | gatto) | la (pecora | capra) ) | lo storione;
GRAMMAR
            )
        );

        $convertedDocument = $this->subject->convert($document);

        $this->assertEquals(
            $convertedDocument->getDefinition('Expected')->getProductions(),
            $convertedDocument->getDefinition('Original')->getProductions()
        );
    }

}
