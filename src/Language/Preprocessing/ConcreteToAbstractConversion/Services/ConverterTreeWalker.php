<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services;

use Polygen\Document;
use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom;
use Polygen\Grammar\Atom\UnfoldableAtom;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Definition;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\NonTerminatingSymbol;
use Polygen\Grammar\Unfoldable\SubproductionUnfoldableType;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\ConverterInterface;

/**
 * This object takes a converter upon construction and can be invoked through the "convert" method, passing a Document in.
 * It will then walk the entire document, applying the converter to all nodes, returning the converted document.
 */
class ConverterTreeWalker implements AbstractSyntaxWalker
{
    /**
     * @var \Polygen\Language\Preprocessing\ConcreteToAbstractConversion\ConverterInterface
     */
    private $converter;

    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @param \Polygen\Document $document
     * @return \Polygen\Document
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
        $node = $this->converter->canConvert($node)
            ? $this->converter->convert($node)
            : $node;
        return $node->traverse($this);
    }

    /**
     * @internal
     * @param Document $document
     * @return Document
     */
    public function walkDocument(Document $document, $_ = null)
    {
        return new Document(
            $this->convertAll($document->getDeclarations())
        );
    }

    /**
     * @internal
     * @param Definition $definition
     * @return Definition
     */
    public function walkDefinition(Definition $definition, $_ = null)
    {
        return new Definition(
            $definition->getName(),
            $this->convertAll($definition->getProductions())
        );
    }

    /**
     * @internal
     * @param Assignment $assignment
     * @return Assignment
     */
    public function walkAssignment(Assignment $assignment, $_ = null)
    {
        return new Assignment(
            $assignment->getName(),
            $this->convertAll($assignment->getProductions())
        );
    }

    /**
     * @internal
     * @param Sequence $sequence
     * @return Sequence
     */
    public function walkSequence(Sequence $sequence, $_ = null)
    {
        return new Sequence(
            $this->convertAll($sequence->getSequenceContents()),
            $sequence->getLabel()
        );
    }

    /**
     * @internal
     * @param Production $production
     * @return Production
     */
    public function walkProduction(Production $production, $_ = null)
    {
        return new Production(
            $this->convertOne($production->getSequence()),
            $production->getFrequencyModifier()
        );
    }

    /**
     * @internal
     * @param Subproduction $subproduction
     * @return Subproduction
     */
    public function walkSubproduction(Subproduction $subproduction, $_ = null)
    {
        return new Subproduction(
            $this->convertAll($subproduction->getDeclarations()),
            $this->convertAll($subproduction->getProductions())
        );
    }

    /**
     * @internal
     * @return Atom\SimpleAtom
     */
    public function walkSimpleAtom(Atom\SimpleAtom $atom, $_ = null)
    {
        return $atom;
    }

    /**
     * @internal
     * @param \Polygen\Grammar\AtomSequence $atomSequence
     * @return mixed|\Polygen\Grammar\AtomSequence
     */
    public function walkAtomSequence(AtomSequence $atomSequence, $_ = null)
    {
        return new AtomSequence(
            $this->convertAll($atomSequence->getAtoms())
        );
    }

    /**
     * @internal
     * @param \Polygen\Grammar\SubproductionUnfoldable $unfoldable
     * @return \Polygen\Grammar\SubproductionUnfoldable
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable, $_ = null)
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
     * @internal
     * @param NonTerminatingSymbol $nonTerminatingSymbol
     * @return NonTerminatingSymbol
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol, $_ = null)
    {
        return $nonTerminatingSymbol;
    }

    /**
     * @internal
     * @return UnfoldableAtom
     */
    public function walkUnfoldableAtom(Atom\UnfoldableAtom $atom, $_ = null)
    {
        return Atom\AtomBuilder::like($atom)->withUnfoldable(
            $this->convertOne($atom->getUnfoldable())
        )->build();
    }

    /**
     * @internal
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
