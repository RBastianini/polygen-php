<?php

namespace Polygen\Language\Interpretation;

use Polygen\Document;
use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom\SimpleAtom;
use Polygen\Grammar\Atom\UnfoldableAtom;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Definition;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\NonTerminatingSymbol;
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Token\Token;

/**
 * Walks a document in concrete syntax and generates a token sequence ready to be condensed into a string.
 * @internal
 */
class TokenSequenceGenerator implements AbstractSyntaxWalker
{
    public function generateSequence(Document $document, Context $context)
    {
        $context = $context->mergeDeclarations($document->getDeclarations());
        return $this->walkOne($document->getDeclaration($context->getStartSymbol()), $context);
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkDocument(Document $document, $context = null)
    {
        throw new \RuntimeException('This method should never be called.');
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkDefinition(Definition $definition, $context = null)
    {
        return $this->walkOneOf($definition->getProductions(), $context);
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
        $context->assign($assignment, $assignmentProduction = $this->walkOneOf($assignment->getProductions(), $context));
        return $assignmentProduction;
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkSequence(Sequence $sequence, $context = null)
    {
        return $this->walkAll($sequence->getSequenceContents(), $context);
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
        return $this->walkAll($production->getSequence()->getSequenceContents(), $context);
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkSubproduction(Subproduction $subproduction, $context = null)
    {
        $context = $context->mergeDeclarations($subproduction->getDeclarations());
        return $this->walkOneOf($subproduction->getProductions(), $context);
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
        return $this->walkOne($atom->getUnfoldable(), $context);
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol, $context = null)
    {
        return $this->walkOne($context->getDeclaration($nonTerminatingSymbol->getToken()->getValue()), $context);
    }

    /**
     * @param Context $context Any context that should be passed ito the wal function.
     * @return Token[]
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable, $context = null)
    {
        return $this->walkOne($unfoldable->getSubproduction(), $context);
    }

    /**
     * @return Token[]
     */
    private function walkOne(Node $node, Context $context)
    {
        return $node->traverse($this, $context);
    }

    /**
     * @param Node[] $nodes
     * @return Token[]
     */
    private function walkOneOf(array $nodes, Context $context)
    {
        $position = $context->getRandomNumber(0, count($nodes) - 1);
        return $this->walkOne($nodes[$position], $context);
    }

    private function walkAll(array $nodes, Context $context)
    {
        return array_merge(
            ...array_map(
                [$this, 'walkOne'],
                $nodes,
                array_fill(0, count($nodes), $context)
            )
        );
    }
}
