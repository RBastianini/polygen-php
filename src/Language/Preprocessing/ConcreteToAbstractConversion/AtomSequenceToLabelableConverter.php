<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Interfaces\HasLabelSelection;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Webmozart\Assert\Assert;

/**
 * Converts an AtomSequence into a HasLabelSelection (unfoldable or atom).
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
     * @return HasLabelSelection
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
        return $node instanceof AtomSequence;
    }

    /**
     * @return HasLabelSelection
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

        return UnfoldableBuilder::get()
            ->simple()
            ->withSubproduction(
                new Subproduction(
                    [],
                    $productions
                )
            )->build();
    }
}