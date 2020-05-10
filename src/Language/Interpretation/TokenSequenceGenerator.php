<?php

namespace Polygen\Language\Interpretation;

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
use Polygen\Language\Token\Token;

/**
 * Walks a document in concrete syntax and generates a token sequence ready to be condensed into a string.
 * @internal
 */
class TokenSequenceGenerator implements AbstractSyntaxWalker
{
    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkDocument(Document $document, $context = null)
    {
        $context = $context->mergeDeclarations($document->getDeclarations());
        return $document->getDeclaration($context->getStartSymbol())->traverse($this, $context);
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkDefinition(Definition $definition, $context = null)
    {
        return $definition->getProductionSet()
            ->whereLabelSelection($context->getLabelSelection())
            ->getRandom($context)
            ->traverse($this, $context);
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkAssignment(Assignment $assignment, $context = null)
    {
        if ($context->isAssigned($assignment)) {
            return $context->getAssigned($assignment);
        }
        $context->assign(
            $assignment,
            $assignmentProduction = $assignment->getProductionSet()
                ->whereLabelSelection($context->getLabelSelection())
                ->getRandom($context)
                ->traverse($this, $context)
        );
        return $assignmentProduction;
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkSequence(Sequence $sequence, $context = null)
    {
        throw new \RuntimeException(
            'We should be skipping a step when walking sequences, by directly getting their contents from the containing production.'
        );
    }

    public function walkAtomSequence(AtomSequence $atoms, $context = null)
    {
        throw new \RuntimeException(
            'There should be no atom sequences at this point. Maybe you forgot to run this document through the converter?'
        );
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkProduction(Production $production, $context = null)
    {
        $results = [];
        foreach ($production->getSequence()->getSequenceContents() as $node) {
            $results[] = $node->traverse($this, $context);
        }
        return array_merge(... $results);
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkSubproduction(Subproduction $subproduction, $context = null)
    {
        $context = $context->mergeDeclarations($subproduction->getDeclarations());
        return $subproduction->getProductionSet()
            ->whereLabelSelection($context->getLabelSelection())
            ->getRandom($context)
            ->traverse($this, $context);
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkSimpleAtom(SimpleAtom $atom, $context = null)
    {
        return [$atom->getToken()];
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkUnfoldableAtom(UnfoldableAtom $atom, $context = null)
    {
        $context = $context->select($atom->getLabelSelection());
        return $atom->getUnfoldable()->traverse($this, $context);
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol, $context = null)
    {
        return $context->getDeclaration($nonTerminatingSymbol->getToken()->getValue())->traverse($this, $context);
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable, $context = null)
    {
        return $unfoldable->getSubproduction()->traverse($this, $context);
    }
}
