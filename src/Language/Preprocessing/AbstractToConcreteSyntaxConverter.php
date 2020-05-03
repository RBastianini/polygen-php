<?php

namespace Polygen\Language\Preprocessing;

use Polygen\Document;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToProductionConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\ConverterInterface;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\DeepUnfoldingConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\FrequencyModifiedSelectionLabelToDotLabelConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\FrequencyModifierProductionConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\IterationUnfoldableConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\OptionalSubproductionToEpsilonAtomConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\PermutationConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\ConverterTreeWalker;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\FrequencyModificationWeightCalculator;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\UnfoldedNonTerminatingSymbolConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\UnfoldedSubproductionConverter;
use Polygen\Language\Preprocessing\Services\IdentifierFactory;
use Webmozart\Assert\Assert;

/**
 * Walks through a Document converting it from abstract to concrete syntax.
 */
class AbstractToConcreteSyntaxConverter
{
    /**
     * @var ConverterInterface[]
     */
    private $converters = [];

    /**
     * @internal Do not call directly. Use the factory method.
     * @param ConverterInterface[] $converters
     */
    public function __construct(array $converters)
    {
        foreach ($converters as $converter) {
            Assert::keyNotExists(
                $this->converters,
                $converter->getPriority(),
                'Multiple converters with the same priority passed.'
            );
            $this->converters[$converter->getPriority()] = $converter;
        }
        ksort($this->converters);
    }

    /**
     * Factory method.
     * @return static
     */
    public static function create()
    {
        $identifierFactory = new IdentifierFactory();
        $frequencyModificationWeightCalculator = new FrequencyModificationWeightCalculator();
        return new static([
            new AtomSequenceToProductionConverter(),
            new FrequencyModifiedSelectionLabelToDotLabelConverter(
                $identifierFactory,
                $frequencyModificationWeightCalculator
            ),
            new FrequencyModifierProductionConverter($frequencyModificationWeightCalculator),
            new OptionalSubproductionToEpsilonAtomConverter(),
            new IterationUnfoldableConverter($identifierFactory),
            new DeepUnfoldingConverter(),
            new PermutationConverter(),
            new UnfoldedSubproductionConverter(),
            new UnfoldedNonTerminatingSymbolConverter()
        ]);
    }

    /**
     * @param Document $document
     * @return Document
     */
    public function convert(Document $document)
    {
        foreach ($this->converters as $converter) {
            $document = (new ConverterTreeWalker($converter))->convert($document);
        }
        return $document;
    }


}
