<?php

namespace Polygen\Language;

use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom\SimpleAtom;
use Polygen\Grammar\Atom\UnfoldableAtom;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Definition;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\NonTerminatingSymbol;

/**
 * Interface for objects that need to traverse the abstract syntax tree.
 */
interface AbstractSyntaxWalker
{
    /**
     * @param mixed $context Any context that should be passed into the walk function.
     * @return mixed
     */
    public function walkDocument(Document $document, $context = null);

    /**
     * @param mixed $context Any context that should be passed into the walk function.
     * @return mixed
     */
    public function walkDefinition(Definition $definition, $context = null);

    /**
     * @param mixed $context Any context that should be passed into the walk function.
     * @return mixed
     */
    public function walkAssignment(Assignment $assignment, $context = null);

    /**
     * @param mixed $context Any context that should be passed into the walk function.
     * @return mixed
     */
    public function walkSequence(Sequence $sequence, $context = null);

    /**
     * @param mixed $context Any context that should be passed into the walk function.
     * @return mixed
     */
    public function walkProduction(Production $production, $context = null);

    /**
     * @param mixed $context Any context that should be passed into the walk function.
     * @return mixed
     */
    public function walkSubproduction(Subproduction $subproduction, $context = null);

    /**
     * @param mixed $context Any context that should be passed into the walk function.
     * @return mixed
     */
    public function walkSimpleAtom(SimpleAtom $atom, $context = null);

    /**
     * @param mixed $context Any context that should be passed into the walk function.
     * @return mixed
     */
    public function walkUnfoldableAtom(UnfoldableAtom $atom, $context = null);

    /**
     * @param mixed $context Any context that should be passed into the walk function.
     * @return mixed
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol, $context = null);

    /**
     * @param mixed $context Any context that should be passed into the walk function.
     * @return mixed
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable, $context = null);
    /**
     * @param mixed $context Any context that should be passed into the walk function.
     * @return mixed
     */
    public function walkAtomSequence(AtomSequence $atoms, $context = null);
}
