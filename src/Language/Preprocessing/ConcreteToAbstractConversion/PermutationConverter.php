<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use drupol\phpermutations\Generators\Permutations;
use Polygen\Grammar\Atom;
use Polygen\Grammar\Atom\AtomBuilder;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\SubproductionUnfoldableType;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\Context;

/**
 * Converts sequences containing permutation unfoldables into plain sequences, by exploding the permutation unfoldables
 * into simple unfoldables.
 *
 * A sequence containing a permutation is in the form:
 * some_terminating_symbol {some permutation unfoldable}.label some_more_terminating_symbol {some other permutation unfoldable}.label
 *
 * where the labels and terminating symbols around the permutations are optional.
 *
 * Such a sequence is converted into a simple unfoldable that contains all possible permutations where every atom that
 * is not a permutation unfoldable is left as is, and every permutation unfoldable is converted into a simple unfoldable,
 * and swapped over to generate all the possible permutations.
 *
 * If this explanation did not make any sense, please
 * @see \Tests\Polygen\Integration\Language\Preprocessing\ConcreteToAbstractConversion\PermutationConverterTest
 */
class PermutationConverter implements ConverterInterface
{

    /**
     * The lowest the number, the highest the priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return 6;
    }

    /**
     * @param Sequence $node
     * @return Node
     */
    public function convert(Node $node, Context $_)
    {
        $permutationSequence = [];
        $permutationPositions = [];

        // Ensure the keys match the array positions
        /** @var Atom[] $originalSequence */
        $originalSequence = array_values($node->getSequenceContents());

        foreach ($originalSequence as $position => $originalSequenceContent) {
            $isPermutation = $originalSequenceContent instanceof Atom\UnfoldableAtom
                && ($unfoldable = $originalSequenceContent->getUnfoldable()) instanceof SubproductionUnfoldable
                && /** @var SubproductionUnfoldable $unfoldable*/ $unfoldable->getType() === SubproductionUnfoldableType::permutation();
            if (!$isPermutation) {
                continue;
            }
            $permutationPositions[] = $position;
            $permutationSequence[] = $originalSequenceContent;
        }

        $converted = [];

        foreach ($this->buildPermutations($permutationSequence)->generator() as $permutation) {
            $converted[] = new Production(
                new Sequence(
                    array_replace($originalSequence, array_combine($permutationPositions, $permutation))
                )
            );
        }

        return new Sequence([
            AtomBuilder::get()
            ->withUnfoldable(
                UnfoldableBuilder::get()
                    ->simple()
                    ->withSubproduction(
                        new Subproduction(
                            [],
                            $converted
                        )
                    )
                ->build()
            )
            ->build()
        ]);

    }

    /**
     * @param Node $node
     * @return bool
     */
    public function canConvert(Node $node)
    {
        if (!$node instanceof Sequence) {
            return false;
        }
        foreach ($node->getSequenceContents() as $sequenceContent) {
            if (
                $sequenceContent instanceof Atom\UnfoldableAtom
                && ($unfoldable = $sequenceContent->getUnfoldable()) instanceof SubproductionUnfoldable
                && $unfoldable->getType() === SubproductionUnfoldableType::permutation()
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param \Polygen\Grammar\Atom\UnfoldableAtom[] $sequenceToPermutate
     * @return Permutations
     */
    private function buildPermutations(array $sequenceToPermutate)
    {
        $sequenceToPermutate = array_map([$this, 'toSimpleUnfoldable'], $sequenceToPermutate);
        return new Permutations($sequenceToPermutate);
    }

    /**
     * @param \Polygen\Grammar\Atom\UnfoldableAtom $atom
     * @return \Polygen\Grammar\Atom\UnfoldableAtom
     */
    private function toSimpleUnfoldable(Atom\UnfoldableAtom $atom)
    {
        return Atom\AtomBuilder::like($atom)
            ->withUnfoldable(
                UnfoldableBuilder::like($atom->getUnfoldable())
                    ->simple()
                    ->build()
            )
        ->build();
    }
}
