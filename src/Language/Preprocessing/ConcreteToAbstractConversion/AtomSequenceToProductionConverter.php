<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Atom;
use Polygen\Grammar\Atom\AtomBuilder;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\ProductionCollection;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Utils\DeclarationCollection;
use Webmozart\Assert\Assert;

/**
 * Converts an AtomSequence into a production.
 *
 * Given a sequence, where some of its contents are AtomSequences, returns a new Sequence built as follows.
 * The start of the new sequence is identical to the old sequence, up to the first AtomSequence. Similarly, the end of
 * the new sequence is identical to the end of the starting sequence, from the next Atom after the last AtomSequence,
 * to the end of the sequence.
 *
 * In the middle, the new sequence contains a Subproduction.
 * This Subproduction contains as many sequences as the cardinality of any (and all) the AtomSequences in the starting
 * sequence.
 * The first Sequence in the Subproduction consists of the first Atom of the first AtomSequence, followed by all the
 * Atoms until the next AtomSequence, then the first Atom of the second AtomSequence, followed by all the Atoms
 * until the next AtomSequence, and so on until the last element is the first Atom of the last AtomSequence.
 * The second Sequence in the Subproduction starts with the second Atom of the first AtomSequence, followed by all
 * the Atoms until the next AtomSequence, then the second Atom of the second AtomSequence, followed by all the Atoms
 * until the next AtomSequence, until the last Atom is the first second Atom of the last AtomSequence.
 * And so on...
 */
class AtomSequenceToProductionConverter implements ConverterInterface
{
    /**
     * The lowest the number, the highest the priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * @param Sequence $sequence
     * @return Sequence
     */
    public function convert(Node $sequence, DeclarationCollection $_)
    {
        Assert::isInstanceOf($sequence, Sequence::class);

        $atomSequencePositions = $this->findAtomSequences($sequence);
        Assert::notEmpty($atomSequencePositions, 'No atom sequences found.');

        // Yes, I'm lazy. I don't care if these array_slice() operations return null, I will filter the unwanted values
        // before merging the arrays together.

        // Sequence contents up to the first AtomSequence can be left intact
        $rebuiltSequence[] = array_slice($sequence->getSequenceContents(), 0, reset($atomSequencePositions));
        $rebuiltSequence[] = [$this->convertAtomSequences($sequence, $atomSequencePositions)];
        // Sequence contents after the last AtomSequence can be left intact.
        $rebuiltSequence[] = array_slice($sequence->getSequenceContents(), end($atomSequencePositions) + 1);

        return new Sequence(
            array_merge(...array_filter($rebuiltSequence)),
            $sequence->getLabel());
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
            if ($sequenceContent instanceof AtomSequence) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the positions in the sequence where the AtomSequences are.
     * @return int[]
     */
    private function findAtomSequences(Sequence $sequence)
    {
        $positions = [];
        foreach ($sequence->getSequenceContents() as $position => $sequenceContent) {
            if ($sequenceContent instanceof AtomSequence) {
                $positions[] = $position;
            }
        }
        return $positions;
    }

    /**
     * Builds the central UnfoldableAtom of the new Sequence. Anything before the first AtomSequence or after the last
     * AtomSequence in the Sequence is not of interest.
     *
     * @param int[] $atomSequencePositions
     * @return Atom
     */
    private function convertAtomSequences(Sequence $sequence, array $atomSequencePositions)
    {
        // The algorithm is as follows:
        // We have to build as many Productions in the Subproduction as the cardinality of any (and all) AtomSequences
        // present, so we repeat the main loop as many times as the cardinality of the AtomSequences.
        // For each iteration n, we iterate the atom sequence positions and take the nth Atom of the first AtomSequence,
        // then take all the Atoms in the original Sequence, up to the next AtomSequence, and then repeat the loop.
        // We can start our work at the first AtomSequence, since everything else before that is not of interest for
        // this method.
        // Similarly, since we can ignore anything that comes after the last AtomSequence, we don't need to any special
        // loop termination business to make sure that we have processed all items, because everything that we have not
        // processed at the end of the array, we don't care about.
        $output = [];
        $sequenceContents = $sequence->getSequenceContents();
        $firstPosition = reset($atomSequencePositions);
        $iterations = count($sequenceContents[$firstPosition]->getAtoms());
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            $currentSequence = [];
            // Ignore anything before the first AtomSequence
            $previousPosition = $firstPosition;
            foreach ($atomSequencePositions as $position) {
                // Don't care if this array_slice operation returns null, I'òò filter out the unwanted values before
                // returning from this method.
                $currentSequence[] = array_slice($sequenceContents, $previousPosition, max(0, $position - $previousPosition));
                // Take the nth element of this AtomSequence.
                $currentSequence[] = [$sequenceContents[$position]->getAtoms()[$iteration]];
                // Make sure to start after the current AtomSequence for the next iteration.
                $previousPosition = $position + 1;
            }
            $output[] = new Production(
                new Sequence(
                    array_merge(...array_filter($currentSequence))
                )
            );
        }

        return AtomBuilder::get()->withUnfoldable(
            UnfoldableBuilder::get()
                ->simple()
                ->withSubproduction(
                    new Subproduction(
                        [],
                        new ProductionCollection($output)
                    )
                )->build()
        )->build();
    }
}
