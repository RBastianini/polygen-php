<?php

namespace Tests\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use Mockery\MockInterface;
use Polygen\Language\Preprocessing\AbstractToConcreteSyntaxConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToProductionConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\FrequencyModifiedSelectionLabelToDotLabelConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\FrequencyModificationWeightCalculator;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\IdentifierFactory;
use Tests\DocumentUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class FrequencyModifiedSelectionLabelToDotLabelConverterTest extends TestCase
{
    use ConverterUtils;
    use DocumentUtils;
    use StreamUtils;

    /**
     * @var IdentifierFactory|MockInterface
     */
    private $identifierFactory;

    /**
     * @var AbstractToConcreteSyntaxConverter
     */
    private $subject;

    protected function setUp()
    {
        parent::setUp();
        $this->identifierFactory = \Mockery::mock(IdentifierFactory::class);
        $this->subject = $this->given_a_converter_with(
            new AtomSequenceToProductionConverter(),
            new FrequencyModifiedSelectionLabelToDotLabelConverter(
                $this->identifierFactory,
                new FrequencyModificationWeightCalculator()
            )
        );
    }

    /**
     * @test
     */
    public function it_converts_a_single_plus_sign()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Original ::= A.(+label1 | label2);
            Expected ::= ( Something ::= A; Something.label1 | Something.label1 | Something.label2);
GRAMMAR
            )
        );

        $this->given_the_generated_id_will_be('Something');

        $convertedDocument = $this->subject->convert($document);

        $this->assertEquals(
            $convertedDocument->getDefinition('Expected')->getProductions(),
            $convertedDocument->getDefinition('Original')->getProductions()
        );
    }


    /**
     * @test
     */
    public function it_converts_a_single_minus_sign()
    {

        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Original ::= A.(label1 | -label2);
            Expected ::= (Something ::= A; Something.label1 | Something.label1 | Something.label2 );
GRAMMAR
            )
        );

        $this->given_the_generated_id_will_be('Something');

        $convertedDocument = $this->subject->convert($document);

        $this->assertEquals(
            $convertedDocument->getDefinition('Expected')->getProductions(),
            $convertedDocument->getDefinition('Original')->getProductions()
        );
    }


    /**
     * @test
     */
    public function it_converts_multiple_plus_signs()
    {

        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Original ::= A.(++label1 | label2);
            Expected ::= ( Something ::= A; Something.label1 | Something.label1 | Something.label1 | Something.label2 );
GRAMMAR
            )
        );

        $this->given_the_generated_id_will_be('Something');

        $convertedDocument = $this->subject->convert($document);

        $this->assertEquals(
            $convertedDocument->getDefinition('Expected')->getProductions(),
            $convertedDocument->getDefinition('Original')->getProductions()
        );
    }

    /**
     * @param string $id
     */
    private function given_the_generated_id_will_be($id)
    {
        $this->identifierFactory->shouldReceive('getId')
            ->andReturn($id);
    }
}
