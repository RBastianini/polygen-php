<?php

namespace Polygen\Language\Preprocessing\StaticCheck;

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
use Polygen\Language\Document;
use Polygen\Language\Errors\ErrorCollection;
use Polygen\Language\Errors\UndeclaredNonTerminatingSymbol;
use Polygen\Utils\DeclarationCollection;

/**
 * Walks the tree in search of non terminating symbols, to verify that they have all been declared.
 */
class NonTerminatingSymbolDeclarationCheck implements StaticCheckInterface, AbstractSyntaxWalker
{
    /**
     * @param Document $document
     * @return ErrorCollection
     */
    public function check(Document $document)
    {
        return $this->walkDocument($document, new DeclarationCollection());
    }

    /**
     * @param Document $document
     * @param DeclarationCollection $context
     * @return ErrorCollection
     * @internal
     */
    public function walkDocument(Document $document, $context = null)
    {
        $context = $context->mergeDeclarations($document->getDeclarations());

        return $this->checkMany($document->getDeclarations(), $context);
    }

    /**
     * @param DeclarationCollection $context
     * @return ErrorCollection
     * @internal
     */
    public function walkDefinition(Definition $definition, $context = null)
    {
        return $this->checkMany($definition->getProductionSet()->getProductions(), $context);
    }

    /**
     * @param DeclarationCollection $context
     * @return ErrorCollection
     * @internal
     */
    public function walkAssignment(Assignment $assignment, $context = null)
    {
        return $this->checkMany($assignment->getProductionSet()->getProductions(), $context);
    }

    /**
     * @param DeclarationCollection $context
     * @return ErrorCollection
     * @internal
     */
    public function walkSequence(Sequence $sequence, $context = null)
    {
        return $this->checkMany($sequence->getSequenceContents(), $context);
    }

    /**
     * @param DeclarationCollection $context
     * @return ErrorCollection
     * @internal
     */
    public function walkProduction(Production $production, $context = null)
    {
        return $this->doCheck($production->getSequence(), $context);
    }

    /**
     * @param DeclarationCollection $context
     * @return ErrorCollection
     * @internal
     */
    public function walkSubproduction(Subproduction $subproduction, $context = null)
    {
        $context = $context->mergeDeclarations($subproduction->getDeclarations());

        return $this->checkMany($subproduction->getProductionSet()->getProductions(), $context);
    }

    /**
     * @param DeclarationCollection $context
     * @return ErrorCollection
     * @internal
     */
    public function walkSimpleAtom(SimpleAtom $atom, $context = null)
    {
        return new ErrorCollection();
    }

    /**
     * @param DeclarationCollection $context
     * @return ErrorCollection
     * @internal
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol, $context = null)
    {
        $errors = [];
        if (!$context->isDeclared($nonTerminatingSymbol->getToken()->getValue())) {
            $errors[] = new UndeclaredNonTerminatingSymbol($nonTerminatingSymbol->getToken());
        }
        return new ErrorCollection($errors);
    }

    /**
     * @param DeclarationCollection $context
     * @return ErrorCollection
     * @internal
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable, $context = null)
    {
        return $this->doCheck($unfoldable->getSubproduction(), $context);
    }

    /**
     * @param DeclarationCollection $context
     * @return ErrorCollection
     * @internal
     */
    public function walkAtomSequence(AtomSequence $atoms, $context = null)
    {
        return $this->checkMany($atoms->getAtoms(), $context);
    }

    /**
     * @param DeclarationCollection $context
     * @return ErrorCollection
     * @internal
     */
    public function walkUnfoldableAtom(UnfoldableAtom $atom, $context = null)
    {
        return $this->doCheck($atom->getUnfoldable(), $context);
    }

    /**
     * @param Node[] $nodes
     * @param mixed $context
     * @return ErrorCollection
     */
    protected function checkMany(array $nodes, $context)
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
    protected function doCheck(Node $node, $context)
    {
        return $node->traverse($this, $context);
    }
}
