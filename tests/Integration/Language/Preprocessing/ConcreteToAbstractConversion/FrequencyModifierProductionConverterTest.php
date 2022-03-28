<?php

namespace Tests\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Language\Preprocessing\AbstractToConcreteSyntaxConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\FrequencyModifierProductionConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\FrequencyModificationWeightCalculator;
use Polygen\Language\Preprocessing\Services\IdentifierFactory;
use Tests\DocumentUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class FrequencyModifierProductionConverterTest extends TestCase
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
        $this->identifierFactory = \Mockery::mock(IdentifierFactory::class);
        $this->subject = $this->given_a_converter_with(
            new FrequencyModifierProductionConverter(
                new FrequencyModificationWeightCalculator()
            )
        );
    }

    /**
     * @test
     */
    public function it_converts_productions_removing_frequency_modifiers()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Original ::= +term1 | term2 | -term3;
            Expected ::= term1 | term1 | term1 | term2 | term2 | term3;
GRAMMAR
            )
        );

        $convertedDocument = $this->subject->convert($document);

        $this->assertEquals(
            $convertedDocument->getDeclaration('Expected')->getProductionSet(),
            $convertedDocument->getDeclaration('Original')->getProductionSet()
        );
    }
}
