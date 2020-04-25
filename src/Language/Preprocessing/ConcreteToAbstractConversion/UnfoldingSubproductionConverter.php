<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Atom;
use Polygen\Grammar\Atom\AtomBuilder;
use Polygen\Grammar\Atom\UnfoldableAtom;
use Polygen\Grammar\FoldingModifier;
use Polygen\Grammar\Interfaces\HasProductions;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\LabelSelection;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;

/**
 * Converts unfolded subproductions by expanding them into their parent sequence.
 *
 * To understand what this class is supposed to achieve, please
 * @see \Tests\Polygen\Integration\Language\Preprocessing\ConcreteToAbstractConversion\UnfoldingSubproductionConverterTest
 */
class UnfoldingSubproductionConverter implements ConverterInterface
{

    /**
     * The lowest the number, the highest the priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return 7;
    }

    /**
     * @param HasProductions $node
     * @return HasProductions
     */
    public function convert(Node $node)
    {
        /** @var \Polygen\Grammar\Interfaces\DeclarationInterface[] $declarationsToSurface */
        $declarationsToSurface = null;

        // Find the subproduction to unfold.
        foreach ($node->getProductions() as $productionIndex => $production) {
            foreach ($production->getSequence()->getSequenceContents() as $sequenceIndex => $atom) {
                if ($this->isUnfoldedSubproductionUnfoldable($atom)) {
                    /** @var UnfoldableAtom $atom */
                    $unfoldable = $atom->getUnfoldable();
                    /** @var SubproductionUnfoldable $unfoldable */
                    $declarationsToSurface = $unfoldable->getSubproduction()
                            ->getDeclarationsOrAssignemnts();
                    break(2);
                }
            }
        }

        // Get all productions before and all productions after the atom to unfold.
        /** @var Production[] $productionsBefore */
        $productionsBefore = array_slice($node->getProductions(), 0, $productionIndex);
        /** @var Production[] $productionsAfter */
        $productionsAfter = array_slice($node->getProductions(), $productionIndex + 1);

        // Explode the sequence of the unfolding subproduction into productions.
        $generatedProductions = $this->explodeSequence(
            $node->getProductions()[$productionIndex]->getSequence(),
            $atom->getLabelSelection(),
            $sequenceIndex
        );

        return $node->withProductions(
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
                                                array_merge(
                                                    $productionsBefore,
                                                    $generatedProductions,
                                                    $productionsAfter
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
        );
    }

    /**
     * @return bool
     */
    public function canConvert(Node $node)
    {
        return $node instanceof HasProductions
            && count(
                array_filter(
                    $node->getProductions(),
                    [$this, 'doesProductionContainUnfoldedSubproduction']
                )
            ) > 0;
    }

    private function doesProductionContainUnfoldedSubproduction(Production $production)
    {
        return count(
                array_filter(
                    $production->getSequence()->getSequenceContents(),
                    [$this, 'isUnfoldedSubproductionUnfoldable']
                )
            ) > 0;
    }

    /**
     * @param Node $node
     * @return bool
     */
    private function isUnfoldedSubproductionUnfoldable(Node $node) {
        return $node instanceof UnfoldableAtom
            && $node->getUnfoldable()->getFoldingModifier() === FoldingModifier::unfold();
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
     * @param \Polygen\Grammar\Production $sequence
     * @param LabelSelection $labelSelection The label selection to apply to all generated unfoldable atoms
     * @param int $unfoldedUnfoldableIndex The position in the production's sequence where the unfolded unfoldable to
     *        convert can be found.
     * @return Production[]
     */
    private function explodeSequence(Sequence $sequence, LabelSelection $labelSelection, $unfoldedUnfoldableIndex)
    {
        /** @var Atom[] $sequenceBefore */
        $sequenceBefore = array_slice($sequence->getSequenceContents(), 0, $unfoldedUnfoldableIndex);
        /** @var Atom[] $sequenceAfter */
        $sequenceAfter = array_slice($sequence->getSequenceContents(), $unfoldedUnfoldableIndex + 1);

        /** @var UnfoldableAtom $unfoldableAtom */
        $unfoldableAtom = $sequence->getSequenceContents()[$unfoldedUnfoldableIndex];
        /** @var SubproductionUnfoldable $subproductionUnfoldable */
        $subproductionUnfoldable = $unfoldableAtom->getUnfoldable();

        $sequences = [];
        foreach ($subproductionUnfoldable->getSubproduction()->getProductions() as $production) {
            $generatedProduction = AtomBuilder::get()
                ->withUnfoldable(
                    UnfoldableBuilder::get()
                        ->simple()
                        ->withSubproduction(
                            new Subproduction(
                                [],
                                [
                                    $production,
                                ]
                            )
                        )
                    ->build()
                )
                ->withLabelSelection($labelSelection)
                ->build();
            $sequences[] = new Production(
                new Sequence(
                    array_merge(
                        $sequenceBefore,
                        [$generatedProduction],
                        $sequenceAfter
                    ),
                    $sequence->getLabel()
                )
            );
        }
        return $sequences;

    }
}
