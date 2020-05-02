<?php

namespace Polygen\Language;

use Polygen\Document;
use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom;
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
     * @param Document $document
     * @param mixed $context Any context that should be passed ito the wal function.
     * @return mixed
     */
    public function walkDocument(Document $document, $context = null);

    /**
     * @param Definition $definition
     * @param mixed $context Any context that should be passed ito the wal function.
     * @return mixed
     */
    public function walkDefinition(Definition $definition, $context = null);

    /**
     * @param Assignment $assignment
     * @param mixed $context Any context that should be passed ito the wal function.
     * @return mixed
     */
    public function walkAssignment(Assignment $assignment, $context = null);

    /**
     * @param Sequence $sequence
     * @param mixed $context Any context that should be passed ito the wal function.
     * @return mixed
     */
    public function walkSequence(Sequence $sequence, $context = null);

    /**
     * @param Production $production
     * @param mixed $context Any context that should be passed ito the wal function.
     * @return mixed
     */
    public function walkProduction(Production $production, $context = null);

    /**
     * @param Subproduction $subproduction
     * @param mixed $context Any context that should be passed ito the wal function.
     * @return mixed
     */
    public function walkSubproduction(Subproduction $subproduction, $context = null);

    /**
     * @param SimpleAtom $atom
     * @param mixed $context Any context that should be passed ito the wal function.
     * @return mixed
     */
    public function walkSimpleAtom(SimpleAtom $atom, $context = null);

    /**
     * @param UnfoldableAtom $atom
     * @param mixed $context Any context that should be passed ito the wal function.
     * @return mixed
     */
    public function walkUnfoldableAtom(UnfoldableAtom $atom, $context = null);

    /**
     * @param \Polygen\Grammar\Unfoldable\NonTerminatingSymbol $nonTerminatingSymbol
     * @param mixed $context Any context that should be passed ito the wal function.
     * @return mixed
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol, $context = null);

    /**
     * @param \Polygen\Grammar\SubproductionUnfoldable $unfoldable
     * @param mixed $context Any context that should be passed ito the wal function.
     * @return mixed
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable, $context = null);

    /**
     * @param \Polygen\Grammar\AtomSequence $atoms
     * @param mixed $context Any context that should be passed ito the wal function.
     * @return mixed
     */
    public function walkAtomSequence(AtomSequence $atoms, $context = null);
}
