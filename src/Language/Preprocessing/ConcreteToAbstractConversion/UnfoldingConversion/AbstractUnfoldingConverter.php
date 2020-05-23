<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion\UnfoldingConversion;

use Polygen\Grammar\Atom;
use Polygen\Grammar\Atom\AtomBuilder;
use Polygen\Grammar\Atom\UnfoldableAtom;
use Polygen\Grammar\FoldingModifier;
use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\HasProductions;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Label;
use Polygen\Grammar\LabelSelection;
use Polygen\Grammar\Production;
use Polygen\Grammar\ProductionCollection;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\ConverterInterface;
use Polygen\Utils\DeclarationCollection;
use Polygen\Utils\LabelSelectionCollection;
use Webmozart\Assert\Assert;

/**
 * Abstract class where the procedures to convert an unfolded unfoldable are shared.
 * This is used by both the UnfoldingSubproductionConverted and the UnfoldedNonTerminatingSymbolConverter, as they
 * both do the same thing, the only part that is different is how determine if they can convert a given unfolded
 * unfoldable and how get the declarations and productions from their unfoldable.
 */
abstract class AbstractUnfoldingConverter implements ConverterInterface
{
    /**
     * @return bool
     */
    public function canConvert(Node $node)
    {
        return $node instanceof HasProductions
            && count(
                array_filter(
                    $node->getProductionSet()->getProductions(),
                    [$this, 'doesProductionContainUnfoldedUnfoldable']
                )
            ) > 0;
    }

    /**
     * @param HasProductions $node
     * @return HasProductions
     */
    public function convert(Node $node, DeclarationCollection $context)
    {
        /** @var \Polygen\Grammar\Interfaces\DeclarationInterface[] $declarationsToSurface */
        $declarationsToSurface = null;

        // Find the unfoldable to unfold.
        foreach ($node->getProductions() as $productionIndex => $production) {
            foreach ($production->getSequence()->getSequenceContents() as $sequenceIndex => $atom) {
                if ($this->isUnfoldedUnfoldable($atom)) {
                    /** @var UnfoldableAtom $atom */
                    $declarationsToSurface = $this->getDeclarationsFromUnfoldable($atom, $context);
                    break(2);
                }
            }
        }

        // Get all productions before and all productions after the atom to unfold.
        /** @var Production[] $productionsBefore */
        $productionsBefore = array_slice($node->getProductionSet()->getProductions(), 0, $productionIndex);
        /** @var Production[] $productionsAfter */
        $productionsAfter = array_slice($node->getProductionSet()->getProductions(), $productionIndex + 1);

        // Explode the sequence of the unfolding subproduction into productions.
        $generatedProductions = $this->convertSequence(
            $context,
            $node->getProductionSet()->getProductions()[$productionIndex]->getSequence(),
            $atom->getLabelSelections(),
            $sequenceIndex
        );

        return $node->withProductions(
            new ProductionCollection(
                [
                    new Production(
                        new Sequence(
                            [
                                AtomBuilder::get()
                                    ->withUnfoldable(
                                        UnfoldableBuilder::get()
                                            ->simple()
                                            ->withSubproduction(
                                                new Subproduction(
                                                    $declarationsToSurface,
                                                    new ProductionCollection(
                                                        array_merge(
                                                            $productionsBefore,
                                                            $generatedProductions,
                                                            $productionsAfter
                                                        )
                                                    )
                                                )
                                            )
                                            ->build()
                                    )
                                    ->build()
                            ]
                        )
                    )
                ]
            )
        );
    }

    /**
     * Checks if a production contains an unfolded unfoldable.
     *
     * @return bool
     */
    protected function doesProductionContainUnfoldedUnfoldable(Production $production)
    {
        return count(
                array_filter(
                    $production->getSequence()->getSequenceContents(),
                    [$this, 'isUnfoldedUnfoldable']
                )
            ) > 0;
    }

    /**
     * Given a sequence and the index where the unfolded unfoldable production to convert is, this function returns an
     * array of productions, each containing sequences having the following structure:
     * All the atoms in the source sequence before unfolded unfoldable,
     * followed by
     * a simple unfoldable containing one of the subproduction of the unfolded unfoldable that needed to be converted,
     * followed by
     * all the atoms in the source sequence after the unfolded unfoldable.
     *
     * From: Label: Atombefore >(One | two | three).selectedLabel AtomAfter
     * To: Label: AtomBefore (One).selectedLabel AtomAfter
     *     | Label: AtomBefore (two).selectedLabel AtomAfter
     *     | Label: AtomBefore (three).selectedLabel AtomAfter
     *
     * @param Sequence $containingSequence The sequence that contains the unfoldable to unfold.
     * @param LabelSelectionCollection $labelSelectionCollection The label selection to apply to all generated unfoldable atoms
     * @param int $unfoldedUnfoldableIndex The position in the production's sequence where the unfolded unfoldable to
     *        convert can be found.
     * @return Production[]
     */
    protected function convertSequence(
        DeclarationCollection $context,
        Sequence $containingSequence,
        LabelSelectionCollection $labelSelectionCollection,
        $unfoldedUnfoldableIndex
    ) {
        /** @var Atom[] $sequenceBefore */
        $sequenceBefore = array_slice($containingSequence->getSequenceContents(), 0, $unfoldedUnfoldableIndex);
        /** @var Atom[] $sequenceAfter */
        $sequenceAfter = array_slice($containingSequence->getSequenceContents(), $unfoldedUnfoldableIndex + 1);

        /** @var UnfoldableAtom $unfoldableAtom */
        $unfoldableAtom = $containingSequence->getSequenceContents()[$unfoldedUnfoldableIndex];

        return $this->buildReplacementProductions(
            $context,
            $sequenceBefore,
            $unfoldableAtom,
            $sequenceAfter,
            $labelSelectionCollection,
            $containingSequence->getLabel()
        );
    }

    /**
     * Builds and returns an array of productions, each built by a sequence where the part before the unfoldable to
     * replace is left intact, then each one having a different production generated by stitching together one
     * production generated from the unfoldable to replace, and the rest of the sequence where the unfoldable to replace.
     *
     * @param Atom[] $sequenceBefore
     * @param UnfoldableAtom $unfoldableToReplace
     * @param Atom[] $sequenceAfter
     * @paral Label|null $label
     * @return Production[]
     */
    private function buildReplacementProductions(
        DeclarationCollection $context,
        array $sequenceBefore,
        UnfoldableAtom $unfoldableToReplace,
        array $sequenceAfter,
        LabelSelectionCollection $labelSelectionCollection,
        Label $label = null
    ) {
        Assert::allIsInstanceOf($sequenceBefore, Atom::class);
        Assert::allIsInstanceOf($sequenceAfter, Atom::class);

        $productions = [];
        foreach ($this->getProductionsFromUnfoldable($unfoldableToReplace, $context) as $production) {
            $productions[] = new Production(
                new Sequence(
                    array_merge(
                        $sequenceBefore,
                        [
                            AtomBuilder::get()
                                ->withUnfoldable(
                                    UnfoldableBuilder::get()
                                        ->simple()
                                        ->withSubproduction(
                                            new Subproduction(
                                                [],
                                                new ProductionCollection(
                                                    [
                                                        $production,
                                                    ]
                                                )
                                            )
                                        )
                                        ->build()
                                )
                                ->withLabelSelections($labelSelectionCollection)
                                ->build()
                        ],
                        $sequenceAfter
                    ),
                    $label
                )
            );
        }
        return $productions;

    }

    /**
     * Checks if a passed parameter is an unfolded unfoldable atom.
     *
     * @return bool
     */
    protected function isUnfoldedUnfoldable(Node $node) {
        return $node instanceof UnfoldableAtom
            && $node->getUnfoldable()->getFoldingModifier() === FoldingModifier::unfold();
    }

    /**
     * Given the unfoldable to unfold and the current context, return the declarations to be surfaced from the
     * unfoldable.
     *
     * @param UnfoldableAtom $unfoldable
     * @return DeclarationInterface[]
     */
    protected abstract function getDeclarationsFromUnfoldable(UnfoldableAtom $unfoldable, DeclarationCollection $context);

    /**
     * Given the unfoldable to unfold and the current context, return the productions that would be generated by the
     * unfoldable.
     *
     * @param UnfoldableAtom $unfoldable
     * @return Production[]
     */
    protected abstract function getProductionsFromUnfoldable(UnfoldableAtom $unfoldable, DeclarationCollection $context);
}
