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
use Polygen\Grammar\SubProduction;
use Polygen\Grammar\Unfoldable;
use Polygen\Grammar\Unfoldable\UnfoldableType;
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\AtomSequenceToLabelableConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\ConverterInterface;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\FrequencyModifiedSelectionLabelToDotLabelConverter;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\FrequencyModifierProductionConverter;
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
        $sequence->getSelectedLabels();
        return new Sequence(
            $this->convertAll($sequence->getLabelables()),
            ($labels = $sequence->getSelectedLabels())
                ? reset($labels)
                : null
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
     * @param SubProduction $subProduction
     * @return SubProduction
     */
    public function walkSubProduction(SubProduction $subProduction)
    {
        return new SubProduction(
            $this->convertAll($subProduction->getDeclarationsOrAssignemnts()),
            $this->convertAll($subProduction->getProductions())
        );
    }

    /**
     * @param \Polygen\Grammar\Atom $atom
     * @return \Polygen\Grammar\Atom
     */
    public function walkAtom(Atom $atom)
    {
        return $atom;
    }

    public function walkAtomSequence(AtomSequence $atomSequence)
    {
        return new AtomSequence(
            $this->convertAll($atomSequence->getAtoms())
        );
    }

    /**
     * @param \Polygen\Grammar\Unfoldable $unfoldable
     * @return \Polygen\Grammar\Unfoldable
     */
    public function walkUnfoldable(Unfoldable $unfoldable)
    {
        switch ($unfoldable->getType()) {
            case UnfoldableType::simple():
                return Unfoldable::simple(
                    $this->convertOne($unfoldable->getSubProduction())
                );
            case UnfoldableType::nonTerminating():
                return $unfoldable;
            case UnfoldableType::permutation():
            case UnfoldableType::deepUnfold():
            case UnfoldableType::optional():
            case UnfoldableType::iteration():
                throw new \RuntimeException('Not implemented (but actually, I believe this should not be needed).');
            default:
                throw new \LogicException('Well how did you get here in the first place?');
        }
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
