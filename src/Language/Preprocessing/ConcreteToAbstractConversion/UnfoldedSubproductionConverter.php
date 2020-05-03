<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Atom\UnfoldableAtom;
use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Language\Context;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\UnfoldingConversion\AbstractUnfoldingConverter;

/**
 * Converts unfolded subproductions by expanding them into their parent sequence.
 *
 * To understand what this class is supposed to achieve, please
 * @see \Tests\Polygen\Integration\Language\Preprocessing\ConcreteToAbstractConversion\UnfoldingSubproductionConverterTest
 */
class UnfoldedSubproductionConverter extends AbstractUnfoldingConverter implements ConverterInterface
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
     * @return bool
     */
    protected function isUnfoldedUnfoldable(Node $node) {
        return parent::isUnfoldedUnfoldable($node)
            // Expand the check to also make sure that the unfoldable is of the expected type.
            && $node->getUnfoldable() instanceof SubproductionUnfoldable;
    }

    /**
     * @param UnfoldableAtom $atom
     * @return DeclarationInterface[]
     */
    protected function getDeclarationsFromUnfoldable(UnfoldableAtom $atom, Context $_)
    {
        return $atom->getUnfoldable()
            ->getSubproduction()
            ->getDeclarations();
    }

    /**
     * @param UnfoldableAtom $atom
     * @return Production[]
     */
    protected function getProductionsFromUnfoldable(UnfoldableAtom $atom, Context $_)
    {
        return $atom->getUnfoldable()
            ->getSubproduction()
            ->getProductions();
    }
}
