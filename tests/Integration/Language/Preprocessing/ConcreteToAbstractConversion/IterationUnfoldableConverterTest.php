<?php

namespace Tests\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use Mockery\MockInterface;
use Polygen\Language\Preprocessing\AbstractToConcreteSyntaxConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToProductionConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\IterationUnfoldableConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\IdentifierFactory;
use Tests\DocumentUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class IterationUnfoldableConverterTest extends TestCase
{
    use ConverterUtils;
    use DocumentUtils;
    use StreamUtils;

    /**
     * @var AbstractToConcreteSyntaxConverter
     */
    private $subject;
    /**
     * @var MockInterface|IdentifierFactory
     */
    private $identifierFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->identifierFactory = \Mockery::mock(IdentifierFactory::class);
        $this->subject = $this->given_a_converter_with(
            new AtomSequenceToProductionConverter(),
            new IterationUnfoldableConverter($this->identifierFactory)
        );
    }

    /**
     * @test
     */
    public function it_converts_iterations_to_assignments_and_productions_that_invoke_themselves_or_return_epsilon()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
            Original ::= (D ::= asd ; P)+;
            Expected ::= (Generated ::= (D ::= asd; P)( _ | Generated); Generated);
GRAMMAR
            )
        );

        $this->identifierFactory->shouldReceive('getId')
            ->once()
            ->andReturn('Generated');

        $convertedDocument = $this->subject->convert($document);

        $this->assertEquals(
            $convertedDocument->getDefinition('Expected')->getProductions(),
            $convertedDocument->getDefinition('Original')->getProductions()
        );
    }
}
