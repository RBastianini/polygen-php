<?php

namespace Polygen\Language;

use Polygen\Document;
use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom;
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
     * @return mixed
     */
    public function walkDocument(Document $document);

    /**
     * @param Definition $definition
     * @return mixed
     */
    public function walkDefinition(Definition $definition);

    /**
     * @param Assignment $assignment
     * @return mixed
     */
    public function walkAssignment(Assignment $assignment);

    /**
     * @param Sequence $sequence
     * @return mixed
     */
    public function walkSequence(Sequence $sequence);

    /**
     * @param Production $production
     * @return mixed
     */
    public function walkProduction(Production $production);

    /**
     * @param Subproduction $subproduction
     * @return mixed
     */
    public function walkSubproduction(Subproduction $subproduction);

    /**
     * @param \Polygen\Grammar\Atom $atom
     * @return mixed
     */
    public function walkAtom(Atom $atom);

    /**
     * @param \Polygen\Grammar\Unfoldable\NonTerminatingSymbol $nonTerminatingSymbol
     * @return mixed
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol);

    /**
     * @param \Polygen\Grammar\SubproductionUnfoldable $unfoldable
     * @return mixed
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable);

    /**
     * @param \Polygen\Grammar\AtomSequence $atoms
     * @return mixed
     */
    public function walkAtomSequence(AtomSequence $atoms);
}
