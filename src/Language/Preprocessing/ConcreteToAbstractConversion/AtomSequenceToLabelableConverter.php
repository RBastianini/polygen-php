<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Interfaces\Labelable;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\SubProduction;
use Polygen\Grammar\Unfoldable;
use Webmozart\Assert\Assert;

/**
 * Converts an AtomSequence into a Labelable (unfoldable or atom).
 *
 * The idea is to go from
 * Label: Something, SomethingElse, SomethingElseEntirely
 * to
 * Label: ( Something | SomethingElse | SomethingElseEntirely )
 */
class AtomSequenceToLabelableConverter implements ConverterInterface
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
     * @param AtomSequence $sequence
     * @return Labelable
     */
    public function convert(Node $sequence)
    {
        Assert::isInstanceOf($sequence, AtomSequence::class);

        return $this->convertAtomSequenceToUnfoldable($sequence);
    }

    /**
     * @param Node $node
     * @return bool
     */
    public function canConvert(Node $node)
    {
        // Checking for the only possible parent node for "AtomSequence", which is "Sequence".
        return $node instanceof AtomSequence;
    }

    /**
     * @return Labelable
     */
    private function convertAtomSequenceToUnfoldable(AtomSequence $atomSequence)
    {
        $productions = [];
        $atoms = $atomSequence->getAtoms();
        if (count($atoms) === 1) {
            return reset($atoms);
        }

        foreach ($atomSequence->getAtoms() as $atom) {
            $productions[] = new Production(
                new Sequence(
                    [$atom]
                )
            );
        }

        return Unfoldable::simple(
                new SubProduction(
                    [],
                    $productions
                )
            );
    }
}
