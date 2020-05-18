<?php

namespace Polygen\Language\Preprocessing\StaticCheck;

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
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Document;
use Polygen\Language\Errors\ErrorCollection;
use Polygen\Language\Errors\MismatchingPositionalGeneratorCardinality;

/**
 * Checks that positional generators in the same sequence have the same cardinality.
 */
class PositionalGeneratorsCardinalityCheck implements StaticCheckInterface, AbstractSyntaxWalker
{

    /**
     * @return ErrorCollection
     */
    public function check(Document $document)
    {
        return $document->traverse($this);
    }

    /**
     * @internal
     * @return ErrorCollection
     */
    public function walkDocument(Document $document, $context = null)
    {
        $errors = new ErrorCollection();
        foreach ($document->getDeclarations() as $declaration) {
            $errors = $errors->merge($declaration->traverse($this));
        }
        return $errors;
    }

    /**
     * @internal
     * @return ErrorCollection
     */
    public function walkDefinition(Definition $definition, $context = null)
    {
        $errors = new ErrorCollection();
        foreach ($definition->getProductionSet()->getProductions() as $production) {
            $errors = $errors->merge($production->traverse($this));
        }
        return $errors;
    }

    /**
     * @internal
     * @return ErrorCollection
     */
    public function walkAssignment(Assignment $assignment, $context = null)
    {
        $errors = new ErrorCollection();
        foreach ($assignment->getProductionSet()->getProductions() as $production) {
            $errors = $errors->merge($production->traverse($this));
        }
        return $errors;
    }

    /**
     * @internal
     * @return ErrorCollection
     */
    public function walkSequence(Sequence $sequence, $context = null)
    {
        $thisErrors = [];
        $otherErrors = new ErrorCollection();
        $positionalGenerationCardinality = 0;
        foreach ($sequence->getSequenceContents() as $sequenceContent) {
            if ($sequenceContent instanceof AtomSequence) {
                $thisPositionalGenerationSequenceCardinality = count($sequenceContent->getAtoms());
                $positionalGenerationCardinality = $positionalGenerationCardinality ?: $thisPositionalGenerationSequenceCardinality;
                if ($positionalGenerationCardinality !== $thisPositionalGenerationSequenceCardinality) {
                    $thisErrors[] = new MismatchingPositionalGeneratorCardinality(
                        $sequenceContent,
                        $positionalGenerationCardinality
                    );
                }
            }
            $otherErrors = $otherErrors->merge($sequenceContent->traverse($this));
        }
        return (new ErrorCollection($thisErrors))->merge($otherErrors);
    }

    /**
     * @internal
     * @return ErrorCollection
     */
    public function walkProduction(Production $production, $context = null)
    {
        return $production->getSequence()->traverse($this);
    }

    /**
     * @internal
     * @return ErrorCollection
     */
    public function walkSubproduction(Subproduction $subproduction, $context = null)
    {
        $errors = new ErrorCollection();
        foreach ($subproduction->getDeclarations() as $declaration) {
            $errors = $errors->merge($declaration->traverse($this));
        }
        foreach ($subproduction->getProductionSet()->getProductions() as $production) {
            $errors = $errors->merge($production->traverse($this));
        }
        return $errors;
    }

    /**
     * @internal
     * @return ErrorCollection
     */
    public function walkSimpleAtom(SimpleAtom $atom, $context = null)
    {
        return new ErrorCollection();
    }

    /**
     * @internal
     * @return ErrorCollection
     */
    public function walkUnfoldableAtom(UnfoldableAtom $atom, $context = null)
    {
        return $atom->getUnfoldable()->traverse($this);
    }

    /**
     * @internal
     * @return ErrorCollection
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol, $context = null)
    {
        return new ErrorCollection();
    }

    /**
     * @internal
     * @return ErrorCollection
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable, $context = null)
    {
        return $unfoldable->getSubproduction()->traverse($this);
    }

    /**
     * @internal
     * @return ErrorCollection
     */
    public function walkAtomSequence(AtomSequence $atoms, $context = null)
    {
        $errors = new ErrorCollection();
        foreach ($atoms->getAtoms() as $atom) {
            $errors = $errors->merge($atom->traverse($this));
        }
        return $errors;
    }
}
