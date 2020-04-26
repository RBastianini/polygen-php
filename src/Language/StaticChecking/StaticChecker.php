<?php

namespace Polygen\Language\StaticChecking;

use Polygen\Document;
use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Definition;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\NonTerminatingSymbol;
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Context;
use Polygen\Language\Errors\ErrorCollection;
use Polygen\Language\Errors\NoStartSymbol;
use Polygen\Language\Errors\UndefinedNonTerminatingSymbol;
use Polygen\Language\Token\Type;

/**
 * Walks down the document and checks that at any time an Non Terminating Symbol is reached, a definition or an
 * assignment are available to resolve it.
 */
class StaticChecker implements AbstractSyntaxWalker
{
    /**
     * @param \Polygen\Document $document
     * @return ErrorCollection
     */
    public function check(Document $document)
    {
        $errors = [];
        if (!$document->isDeclared(Document::START)) {
            $errors[] = new NoStartSymbol();
        }

        return (new ErrorCollection($errors))->merge(
            $this->walkDocument($document, new Context())
        );
    }

    /**
     * @internal
     * @param \Polygen\Document $document
     * @param Context $context
     * @return ErrorCollection
     */
    public function walkDocument(Document $document, $context = null)
    {
        $context = $context->mergeAssignments($document->getAssignments())
            ->mergeDefinitions($document->getDefinitions());

        $errors = $this->checkMany($document->getDefinitions(), $context)
            ->merge($this->checkMany($document->getAssignments(), $context));

        return $errors;
    }

    /**
     * @internal
     * @param \Polygen\Grammar\Definition $definition
     * @param Context $context
     * @return ErrorCollection
     */
    public function walkDefinition(Definition $definition, $context = null)
    {
        return $this->checkMany($definition->getProductions(), $context);
    }

    /**
     * @internal
     * @param \Polygen\Grammar\Assignment $assignment
     * @param Context $context
     * @return ErrorCollection
     */
    public function walkAssignment(Assignment $assignment, $context = null)
    {
        return $this->checkMany($assignment->getProductions(), $context);
    }

    /**
     * @internal
     * @param \Polygen\Grammar\Sequence $sequence
     * @param Context $context
     * @return ErrorCollection
     */
    public function walkSequence(Sequence $sequence, $context = null)
    {
        return $this->checkMany($sequence->getSequenceContents(), $context);
    }

    /**
     * @internal
     * @param \Polygen\Grammar\Production $production
     * @param Context $context
     * @return ErrorCollection
     */
    public function walkProduction(Production $production, $context = null)
    {
        return $this->doCheck($production->getSequence(), $context);
    }

    /**
     * @internal
     * @param \Polygen\Grammar\Subproduction $subproduction
     * @param Context $context
     * @return ErrorCollection
     */
    public function walkSubproduction(Subproduction $subproduction, $context = null)
    {
        $context = $context->mergeDefinitions($subproduction->getDefinitions())
            ->mergeAssignments($subproduction->getAssignments());

        return $this->checkMany($subproduction->getProductions(), $context);
    }

    /**
     * @internal
     * @param \Polygen\Grammar\Atom $atom
     * @param Context $context
     * @return ErrorCollection
     */
    public function walkAtom(Atom $atom, $context = null)
    {
        if ($atom instanceof Atom\UnfoldableAtom) {
            return $this->doCheck($atom->getUnfoldable(), $context);
        }

        return new ErrorCollection();

    }

    /**
     * @internal
     * @param \Polygen\Grammar\Unfoldable\NonTerminatingSymbol $nonTerminatingSymbol
     * @param Context $context
     * @return ErrorCollection
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol, $context = null)
    {
        $token = $nonTerminatingSymbol->getToken();
        $errors = [];
        if ($token->getType() === Type::nonTerminatingSymbol() && !$context->isDeclared($token->getValue())) {
            $errors[] = new UndefinedNonTerminatingSymbol($token);
        }
        return new ErrorCollection($errors);
    }

    /**
     * @internal
     * @param \Polygen\Grammar\SubproductionUnfoldable $unfoldable
     * @param Context $context
     * @return ErrorCollection
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable, $context = null)
    {
        return $this->doCheck($unfoldable->getSubproduction(), $context);
    }

    /**
     * @internal
     * @param \Polygen\Grammar\AtomSequence $atoms
     * @param Context $context
     * @return ErrorCollection
     */
    public function walkAtomSequence(AtomSequence $atoms, $context = null)
    {
        return $this->checkMany($atoms->getAtoms(), $context);
    }

    /**
     * @param Node[] $nodes
     * @param Context $context
     * @return ErrorCollection
     */
    private function checkMany(array $nodes, Context $context)
    {
        return array_reduce(
            array_map([$this, 'doCheck'], $nodes, array_fill(0, count($nodes), $context)),
            function (ErrorCollection $anErrorCollection, ErrorCollection $anotherErrorCollection) {
                return $anErrorCollection->merge($anotherErrorCollection);
            },
            new ErrorCollection()
        );
    }

    /**
     * @return ErrorCollection
     */
    private function doCheck(Node $node, $context)
    {
        return $node->traverse($this, $context);
    }
}
