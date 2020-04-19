<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Document;
use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Definition;
use Polygen\Grammar\FoldingModifier;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\NonTerminatingSymbol;
use Polygen\Grammar\Unfoldable\SubproductionUnfoldableType;
use Polygen\Grammar\Unfoldable\Unfoldable;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\AbstractSyntaxWalker;

/**
 * Converts unfoldable sub productions having a deep unfolding modifier to simple subproductions where every folded
 * unfoldable into a simple unfoldable, and where every simple unfoldable is turned into an unfolding unfoldable.
 *
 * Since this class needs to recursively walk the tree, it also implements AbstractSyntaxWalker.
 */
class DeepUnfoldingConverter implements ConverterInterface, AbstractSyntaxWalker
{

    /**
     * The lowest the number, the highest the priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return 5;
    }

    /**
     * @param SubproductionUnfoldable $subproductionUnfoldable
     * @return SubproductionUnfoldable
     */
    public function convert(Node $subproductionUnfoldable)
    {
        // Convert this non deep unfolding unfoldable into a simple unfoldable, and recursively walk down this
        // unfoldable to
        return UnfoldableBuilder::like($subproductionUnfoldable)
            ->simple()
            ->withSubproduction($subproductionUnfoldable->getSubproduction()->traverse($this))
            ->build();
    }

    /**
     * @param Node $node
     * @return bool
     */
    public function canConvert(Node $node)
    {
        return $node instanceof SubproductionUnfoldable
            && $node->getType() === SubproductionUnfoldableType::deepUnfold();
    }

    /**
     * @return mixed
     */
    public function walkDocument(Document $document)
    {
        throw new \RuntimeException('It should not be possible to reach a document from an unfoldable.');
    }

    /**
     * @return Definition
     */
    public function walkDefinition(Definition $definition)
    {
        return new Definition($definition->getName(), $this->convertMany($definition->getProductions()));
    }

    /**
     * @return Assignment
     */
    public function walkAssignment(Assignment $assignment)
    {
        return new Assignment($assignment->getName(), $this->convertMany($assignment->getProductions()));
    }

    /**
     * @return Sequence
     */
    public function walkSequence(Sequence $sequence)
    {
        return new Sequence($this->convertMany($sequence->getSequenceContents()), $sequence->getLabel());
    }

    /**
     * @return Production
     */
    public function walkProduction(Production $production)
    {
        return new Production($this->convertOne($production->getSequence()), $production->getFrequencyModifier());
    }

    /**
     * @return Subproduction
     */
    public function walkSubproduction(Subproduction $subproduction)
    {
        return new Subproduction(
            $this->convertMany($subproduction->getDeclarationsOrAssignemnts()),
            $this->convertMany($subproduction->getProductions())
        );
    }

    /**
     * @return Atom
     */
    public function walkAtom(Atom $atom)
    {
        return $atom instanceof Atom\SimpleAtom
            ? $atom
            : Atom\AtomBuilder::like($atom)
                ->withUnfoldable(
                    $this->convertOne($atom->getUnfoldable())
                )->build();
    }

    /**
     * @return NonTerminatingSymbol
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol)
    {
        return $this->convertUnfoldables($nonTerminatingSymbol);
    }

    /**
     * @return SubproductionUnfoldable
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable)
    {
        return $this->convertUnfoldables($unfoldable);
    }

    /**
     * @return AtomSequence
     */
    public function walkAtomSequence(AtomSequence $atoms)
    {
        return new AtomSequence($this->convertMany($atoms->getAtoms()));
    }

    /**
     * Utility method to convert nested unfoldables:
     *
     * simple unfoldables => unfolded unfoldables,
     * folded unfoldables => simple unfoldables.
     *
     * @return NonTerminatingSymbol|SubproductionUnfoldable
     */
    private function convertUnfoldables(Unfoldable $unfoldable)
    {
        $factory = UnfoldableBuilder::like($unfoldable);
        return $unfoldable->getFoldingModifier() === FoldingModifier::fold()
            ? $factory->withFoldingModifier(null)
                ->build()
            : $factory->withFoldingModifier(FoldingModifier::unfold())
                ->build();
    }

    /**
     * Traverses and converts multiple nodes.
     *
     * @param Node[]
     * @return Node[]
     */
    private function convertMany(array $nodes)
    {
        return array_map(
            function (Node $node) {
                return $node->traverse($this);
            },
            $nodes
        );
    }

    /**
     * Alias of the node traverse method.
     *
     * @return Node
     */
    private function convertOne(Node $node)
    {
        return $node->traverse($this);
    }
}
