<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Atom\UnfoldableAtom;
use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Unfoldable\NonTerminatingSymbol;
use Polygen\Language\Context;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\UnfoldingConversion\AbstractUnfoldingConverter;

/**
 * This converter replaces unfolded non-terminating symbols with a production generated from their declaration.
 */
class UnfoldedNonTerminatingSymbolConverter extends AbstractUnfoldingConverter implements ConverterInterface
{
    /**
     * The lowest the number, the highest the priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return 8;
    }

    /**
     * @return bool
     */
    protected function isUnfoldedUnfoldable(Node $node) {
        return parent::isUnfoldedUnfoldable($node)
            // Expand the check to also make sure that the unfoldable is of the expected type.
            && $node->getUnfoldable() instanceof NonTerminatingSymbol;
    }

    /**
     * @return DeclarationInterface[]
     */
    protected function getDeclarationsFromUnfoldable(UnfoldableAtom $unfoldable, Context $context)
    {
        // Although the documentation of the conversion step dictates that any declaration should be surfaced to the
        // containing sequence (and, consequently, returned here), I believe this to be not necessary in practice.
        // The reason I think so is because, say X is the symbol we are unfolding, either X is a declaration in the root
        // scope, or X is a declaration in a subproduction.
        // If X is declared in the root scope, then it cannot contain a declaration itself, so there is nothing to
        // surface.
        // If X is a subproduction, then it must be declared in the same scope or a parent scope of where it is
        // referenced, otherwise the grammar would not be valid. If it is declared in a parent scope, then there's
        // no need to surface the definition. The remaining possibility is that it is declared in the current scope, so
        // for this reason there is no other declaration to surface, because the declaration will be brought back with
        // the production that contain it.
        return [];
    }

    /**
     * @return Production[]
     */
    protected function getProductionsFromUnfoldable(UnfoldableAtom $unfoldable, Context $context)
    {
        return $context->getDeclaration($unfoldable->getUnfoldable()->getToken()->getValue())->getProductions();
    }
}
