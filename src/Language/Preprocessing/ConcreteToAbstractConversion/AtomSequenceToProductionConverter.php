<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Atom;
use Polygen\Grammar\Atom\AtomBuilder;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\Context;
use Webmozart\Assert\Assert;

/**
 * Converts an AtomSequence into a production.
 *
 * The idea is to go from
 * Label: Something, SomethingElse, SomethingElseEntirely
 * to
 * Label: ( Something | SomethingElse | SomethingElseEntirely )
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
     * @param AtomSequence $sequence
     * @return Atom
     */
    public function convert(Node $sequence, Context $_)
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
     * @return Atom
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

        return AtomBuilder::get()->withUnfoldable(
            UnfoldableBuilder::get()
            ->simple()
            ->withSubproduction(
                new Subproduction(
                    [],
                    $productions
                )
            )->build()
        )->build();
    }
}
