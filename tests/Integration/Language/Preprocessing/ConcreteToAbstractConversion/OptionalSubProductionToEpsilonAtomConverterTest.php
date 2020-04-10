<?php

namespace Tests\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use PHPUnit\Framework\TestCase;
use Polygen\Language\Preprocessing\AbstractToConcreteSyntaxConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToLabelableConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\OptionalSubProductionToEpsilonAtomConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\IdentifierFactory;
use Tests\DocumentUtils;
use Tests\StreamUtils;

class OptionalSubProductionToEpsilonAtomConverterTest extends TestCase
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
            new AtomSequenceToLabelableConverter(),
            new OptionalSubProductionToEpsilonAtomConverter()
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
            Original ::= [Nonterm ::= prod; Nonterm term];
            Expected ::= ( _ | (Nonterm ::= prod; Nonterm term));
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
