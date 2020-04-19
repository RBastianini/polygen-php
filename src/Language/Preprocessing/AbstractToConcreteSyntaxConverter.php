<?php

namespace Polygen\Language\Preprocessing;

use Polygen\Document;
use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Definition;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\NonTerminatingSymbol;
use Polygen\Grammar\Unfoldable\SubproductionUnfoldableType;
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToLabelableConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\ConverterInterface;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\DeepUnfoldingConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\FrequencyModifiedSelectionLabelToDotLabelConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\FrequencyModifierProductionConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\IterationUnfoldableConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\OptionalSubproductionToEpsilonAtomConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\FrequencyModificationWeightCalculator;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\IdentifierFactory;
use Webmozart\Assert\Assert;

/**
 * Walks through a Document converting it from abstract to concrete syntax.
 */
class AbstractToConcreteSyntaxConverter implements AbstractSyntaxWalker
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
            new AtomSequenceToLabelableConverter(),
            new FrequencyModifiedSelectionLabelToDotLabelConverter(
                $identifierFactory,
                $frequencyModificationWeightCalculator
            ),
            new FrequencyModifierProductionConverter($frequencyModificationWeightCalculator),
            new OptionalSubproductionToEpsilonAtomConverter(),
            new IterationUnfoldableConverter($identifierFactory),
            new DeepUnfoldingConverter(),
        ]);
    }

    /**
     * @param Document $document
     * @return Document
     */
    public function convert(Document $document)
    {
        return $this->convertOne($document);
    }

    /**
     * @param Node $node
     * @return Node
     */
    private function convertOne(Node $node)
    {

        foreach ($this->converters as $converter) {
            $node = $converter->canConvert($node)
                ? $converter->convert($node)
                : $node;
        }
        return $node->traverse($this);
    }

    /**
     * @param Document $document
     * @return Document
     */
    public function walkDocument(Document $document)
    {
        return new Document(
            $this->convertAll($document->getDefinitions()),
            $this->convertAll($document->getAssignments())
        );
    }

    /**
     * @param Definition $definition
     * @return Definition
     */
    public function walkDefinition(Definition $definition)
    {
        return new Definition(
            $definition->getName(),
            $this->convertAll($definition->getProductions())
        );
    }

    /**
     * @param Assignment $assignment
     * @return Assignment
     */
    public function walkAssignment(Assignment $assignment)
    {
        return new Assignment(
            $assignment->getName(),
            $this->convertAll($assignment->getProductions())
        );
    }

    /**
     * @param Sequence $sequence
     * @return Sequence
     */
    public function walkSequence(Sequence $sequence)
    {
        return new Sequence(
            $this->convertAll($sequence->getSequenceContents()),
            $sequence->getLabel()
        );
    }

    /**
     * @param Production $production
     * @return Production
     */
    public function walkProduction(Production $production)
    {
        return new Production(
            $this->convertOne($production->getSequence()),
            $production->getFrequencyModifier()
        );
    }

    /**
     * @param Subproduction $subproduction
     * @return Subproduction
     */
    public function walkSubproduction(Subproduction $subproduction)
    {
        return new Subproduction(
            $this->convertAll($subproduction->getDeclarationsOrAssignemnts()),
            $this->convertAll($subproduction->getProductions())
        );
    }

    /**
     * @param \Polygen\Grammar\Atom $atom
     * @return \Polygen\Grammar\Atom
     */
    public function walkAtom(Atom $atom)
    {
        return $atom instanceof Atom\SimpleAtom
            ? $atom
            : Atom\AtomBuilder::like($atom)->withUnfoldable(
                $this->convertOne($atom->getUnfoldable())
            )->build();
    }

    public function walkAtomSequence(AtomSequence $atomSequence)
    {
        return new AtomSequence(
            $this->convertAll($atomSequence->getAtoms())
        );
    }

    /**
     * @param \Polygen\Grammar\SubproductionUnfoldable $unfoldable
     * @return \Polygen\Grammar\SubproductionUnfoldable
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable)
    {
        switch ($unfoldable->getType()) {
            case SubproductionUnfoldableType::simple():
                return UnfoldableBuilder::like($unfoldable)->withSubproduction(
                    $this->convertOne($unfoldable->getSubproduction())
                )->build();
            case SubproductionUnfoldableType::optional():
                return UnfoldableBuilder::like($unfoldable)->withSubproduction(
                    $this->convertOne($unfoldable->getSubproduction())
                )->build();
            case SubproductionUnfoldableType::permutation():
            case SubproductionUnfoldableType::deepUnfold():
            case SubproductionUnfoldableType::iteration():
                return UnfoldableBuilder::like($unfoldable)
                    ->withSubproduction(
                        $this->convertOne($unfoldable->getSubproduction())
                    )
                    ->build();
            default:
                throw new \LogicException('Well how did you get here in the first place?');
        }
    }

    /**
     * @param NonTerminatingSymbol $nonTerminatingSymbol
     * @return NonTerminatingSymbol
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol)
    {
        return $nonTerminatingSymbol;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    private function convertAll(array $nodes)
    {
        return array_map(
            [$this, 'convertOne'],
            $nodes
        );
    }
}
