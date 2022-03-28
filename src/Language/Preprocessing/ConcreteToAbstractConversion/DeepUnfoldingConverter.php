<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Definition;
use Polygen\Grammar\FoldingModifier;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\ProductionCollection;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\NonTerminatingSymbol;
use Polygen\Grammar\Unfoldable\SubproductionUnfoldableType;
use Polygen\Grammar\Unfoldable\Unfoldable;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Document;
use Polygen\Utils\DeclarationCollection;

/**
 * Converts unfoldable sub productions having a deep unfolding modifier to simple subproductions where every folded
 * unfoldable is converted into a simple unfoldable, and where every simple unfoldable is turned into an unfolding
 * unfoldable.
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
    public function convert(Node $subproductionUnfoldable, DeclarationCollection $_)
    {
        // Convert this non deep unfolding unfoldable into a simple unfoldable, and recursively walk down this
        // unfoldable.
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
     * @internal
     * @return mixed
     */
    public function walkDocument(Document $document, $_ = null)
    {
        throw new \RuntimeException('It should not be possible to reach a document from an unfoldable.');
    }

    /**
     * @internal
     * @return Definition
     */
    public function walkDefinition(Definition $definition, $_ = null)
    {
        return new Definition(
            $definition->getName(),
            new ProductionCollection(
                array_map(
                    [$this, 'walkProduction'],
                    $definition->getProductionSet()
                        ->getProductions()
                )
            )
        );
    }

    /**
     * @internal
     * @return Assignment
     */
    public function walkAssignment(Assignment $assignment, $_ = null)
    {
        return new Assignment(
            $assignment->getName(),
            new ProductionCollection(
                array_map(
                    [$this, 'walkProduction'],
                    $assignment->getProductionSet()
                        ->getProductions()
                )
            )
        );
    }

    /**
     * @internal
     * @return Sequence
     */
    public function walkSequence(Sequence $sequence, $_ = null)
    {
        return new Sequence(
            array_map(
                function (Node $node) {
                    return $node->traverse($this);
                },
                $sequence->getSequenceContents()
            ),
            $sequence->getLabel()
        );
    }

    /**
     * @internal
     * @return Production
     */
    public function walkProduction(Production $production, $_ = null)
    {
        return new Production($production->getSequence()->traverse($this), $production->getFrequencyModifier());
    }

    /**
     * @internal
     * @return Subproduction
     */
    public function walkSubproduction(Subproduction $subproduction, $_ = null)
    {
        return new Subproduction(
            array_map(
                function (Node $node) {
                    return $node->traverse($this);
                },
                $subproduction->getDeclarations()
            ),
            new ProductionCollection(
                array_map(
                    function (Node $node) {
                        return $node->traverse($this);
                    },
                    $subproduction->getProductionSet()
                        ->getProductions()
                )
            )
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
     * @return NonTerminatingSymbol
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol, $_ = null)
    {
        return $this->convertUnfoldables($nonTerminatingSymbol);
    }

    /**
     * @internal
     * @return SubproductionUnfoldable
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable, $_ = null)
    {
        return $this->convertUnfoldables($unfoldable);
    }

    /**
     * @internal
     * @return AtomSequence
     */
    public function walkAtomSequence(AtomSequence $atoms, $_ = null)
    {
        throw new \RuntimeException('There should be no atom sequences at this point.');
    }

    /**
     * @internal
     * @return Atom\UnfoldableAtom
     */
    public function walkUnfoldableAtom(Atom\UnfoldableAtom $atom, $_ = null)
    {
        return Atom\AtomBuilder::like($atom)
            ->withUnfoldable(
                $atom->getUnfoldable()->traverse($this)
            )->build();
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
}
