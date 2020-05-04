<?php

namespace Polygen\Language\Preprocessing\StaticCheck\InfiniteRecursionCheck;

use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom;
use Polygen\Grammar\Atom\UnfoldableAtom;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Definition;
use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\NonTerminatingSymbol;
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Context;
use Polygen\Language\Document;
use Polygen\Language\Preprocessing\Services\IdentifierFactory;
use Webmozart\Assert\Assert;

/**
 * Given a document, constructs and return the reference graph of the declarations.
 */
class ReferenceGraphBuilder implements AbstractSyntaxWalker
{
    /**
     * @var ReferenceGraph
     */
    private $referenceGraph;

    /**
     * @var IdentifierFactory
     */
    private $identifierFactory;

    public function __construct(IdentifierFactory $identifierFactory)
    {
        $this->referenceGraph = new ReferenceGraph();
        $this->identifierFactory = $identifierFactory;
    }

    /**
     * @param Document $document
     * @return ReferenceGraph
     */
    public function build(Document $document)
    {
        $this->walkDocument($document);

        return $this->referenceGraph;
    }

    /**
     * @param DeclarationsContext $context
     * @return void
     *@internal
     */
    public function walkDocument(Document $document, $context = null)
    {
        $context = DeclarationsContext::root(new Context($document->getDeclarations()));

        $this->buildManyReferenceGraphs($document->getDeclarations(), $context);
    }

    /**
     * @param DeclarationsContext $context
     * @return string[]
     *@internal
     */
    public function walkDefinition(Definition $definition, $context = null)
    {
       return $this->walkMany($definition->getProductions(), $context);
    }

    /**
     * @param DeclarationsContext $context
     * @return string[]
     *@internal
     */
    public function walkAssignment(Assignment $assignment, $context = null)
    {
        return $this->walkMany($assignment->getProductions(), $context);
    }

    /**
     * @param DeclarationsContext $context
     * @return string[]
     *@internal
     */
    public function walkSequence(Sequence $sequence, $context = null)
    {
        return $this->walkMany($sequence->getSequenceContents(), $context);
    }

    /**
     * @param DeclarationsContext $context
     * @return string[]
     *@internal
     */
    public function walkProduction(Production $production, $context = null)
    {
        return $this->walkOne($production->getSequence(), $context);
    }

    /**
     * @param DeclarationsContext $context
     * @return string[]
     *@internal
     */
    public function walkSubproduction(Subproduction $subproduction, $context = null)
    {
        $context = $context->addDeclarations(
            new Context($subproduction->getDeclarations()),
            $this->identifierFactory
        );

        $this->buildManyReferenceGraphs($subproduction->getDeclarations(), $context);

        return $this->walkMany($subproduction->getProductions(), $context);
    }

    /**
     * @param DeclarationsContext $context
     * @return string[]
     *@internal
     */
    public function walkSimpleAtom(Atom\SimpleAtom $atom, $context = null)
    {
        return [ReferenceGraph::TERMINATING_SYMBOL => ReferenceGraph::TERMINATING_SYMBOL];
    }

    /**
     * @param DeclarationsContext $context
     * @return string[]
     *@internal
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol, $context = null)
    {
        $uniqueName = $context->getUniqueName($nonTerminatingSymbol->getToken()->getValue());
        return [$uniqueName => $uniqueName];
    }

    /**
     * @param DeclarationsContext $context
     * @return string[]
     *@internal
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable, $context = null)
    {
        return $this->walkOne($unfoldable->getSubproduction(), $context);
    }

    /**
     * @param DeclarationsContext $context
     * @return string[]
     *@internal
     */
    public function walkAtomSequence(AtomSequence $atoms, $context = null)
    {
        return $this->walkMany($atoms->getAtoms(), $context);
    }

    /**
     * @param DeclarationsContext $context
     * @return string[]
     *@internal
     */
    public function walkUnfoldableAtom(UnfoldableAtom $atom, $context = null)
    {
        /** @var UnfoldableAtom $atom */
        return $this->walkOne($atom->getUnfoldable(), $context);
    }

    /**
     * Populates the declarationStructuresByUniqueName and declarationsByUniqueName arrays for a given declaration.
     *
     * @param DeclarationInterface|Node $declaration
     * @param DeclarationsContext $existingDeclarations
     * @return void
     */
    private function resolveDeclarationStructure(DeclarationInterface $declaration, DeclarationsContext $existingDeclarations)
    {
        Assert::isInstanceOf($declaration, Node::class);

        $this->referenceGraph->addReferences($declaration, $existingDeclarations, $this->walkOne($declaration, $existingDeclarations));
    }

    /**
     * Populates the declarationStructuresByUniqueName and declarationsByUniqueName arrays for a series of declarations.
     *
     * @param DeclarationInterface[] $declarations
     * @param DeclarationsContext $existingDeclarations
     */
    private function buildManyReferenceGraphs(array $declarations, DeclarationsContext $existingDeclarations)
    {
        array_map(
            [$this, 'resolveDeclarationStructure'],
            $declarations,
            array_fill(0, count($declarations), $existingDeclarations)
        );
    }

    /**
     * Traverse a node and returns the referenced symbols.
     *
     * @param Node $node
     * @param DeclarationsContext $declarations
     * @return string[]
     */
    private function walkOne(Node $node, DeclarationsContext $declarations)
    {
        return $node->traverse($this, $declarations);
    }

    /**
     * Traverses an array of nodes and returns their referenced symbols.
     *
     * @param Node[] $nodes
     * @param DeclarationsContext $declarations
     * @return string[]
     */
    private function walkMany(array $nodes, DeclarationsContext $declarations)
    {
        return array_merge(
            ...array_map(
                [$this, 'walkOne'],
                $nodes,
                array_fill(0, count($nodes), $declarations)
            )
        );
    }
}
