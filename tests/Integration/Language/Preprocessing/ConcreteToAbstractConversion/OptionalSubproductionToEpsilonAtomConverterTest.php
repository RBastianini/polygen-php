<?php

namespace Tests\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Language\Preprocessing\AbstractToConcreteSyntaxConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToProductionConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\OptionalSubproductionToEpsilonAtomConverter;
use Tests\DocumentUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class OptionalSubproductionToEpsilonAtomConverterTest extends TestCase
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
            new AtomSequenceToProductionConverter(),
            new OptionalSubproductionToEpsilonAtomConverter()
        );
    }

    /**
     * @test
     */
    public function it_converts_optional_subproductions_to_simple_subproductions_with_an_epsilon()
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
            $convertedDocument->getDeclaration('Expected')->getProductions(),
            $convertedDocument->getDeclaration('Original')->getProductions()
        );
    }
}
