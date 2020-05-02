<?php

namespace Polygen\Language\Preprocessing\StaticCheck;

use Polygen\Document;
use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Language\Errors\ErrorCollection;
use Polygen\Language\Errors\InfiniteRecursion;
use Polygen\Language\Preprocessing\Services\IdentifierFactory;
use Polygen\Language\Preprocessing\StaticCheck\InfiniteRecursionCheck\ReferenceGraph;
use Polygen\Language\Preprocessing\StaticCheck\InfiniteRecursionCheck\ReferenceGraphBuilder;

/**
 * Checks that for each declaration it exists a way for each to eventually terminate. This does not guarantee that the
 * declaration will actually result in a termination, since there might still be ways for it to enter a "bad path" that
 * would not be detected by this check.
 *
 * The original Polygen, despite the documentation stating otherwise, does not seem to report any of these issues, so
 * for now I'm satisfied with just checking the certain infinite loops.
 */
class InfiniteRecursionCheck implements StaticCheckInterface
{
    /**
     * An array representing a single reference to a terminating symbol.
     */
    const TERMINATING_REFERENCE = [ReferenceGraph::TERMINATING_SYMBOL => ReferenceGraph::TERMINATING_SYMBOL];

    /**
     * @var ReferenceGraphBuilder
     */
    private $referenceGraphBuilder;

    public function __construct(IdentifierFactory $identifierFactory)
    {
        $this->referenceGraphBuilder = new ReferenceGraphBuilder($identifierFactory);
    }

    /**
     * @return \Polygen\Language\Errors\ErrorCollection
     */
    public function check(Document $document)
    {
        $referenceGraph = $this->referenceGraphBuilder->build($document);

        $errors = [];
        foreach ($this->findInfiniteLoops($referenceGraph) as $declaration) {
            $errors[] = new InfiniteRecursion($declaration);
        }
        return new ErrorCollection($errors);
    }

    /**
     * @param ReferenceGraph $referenceGraph
     * @return DeclarationInterface[]
     */
    private function findInfiniteLoops(ReferenceGraph $referenceGraph)
    {
        // Initialize the $declarations and $previousDeclarations arrays. For each declaration containing a
        // terminating symbol, clear out all other references and just leave the terminating symbols.
        // For each declaration referencing a terminating declaration, clear out its references and replace them with
        // a terminating symbol reference.
        // Continue the loop until the two arrays remain the same after iterating through them.
        $declarations = $referenceGraph->getReferencedDeclarationsByUniqueName();
        do {
            $previousDeclarations = $declarations;
            foreach ($declarations as $declaration => $references) {
                if (array_key_exists(ReferenceGraph::TERMINATING_SYMBOL, $references)) {
                    $declarations[$declaration] = self::TERMINATING_REFERENCE;
                    continue;
                }
                foreach ($references as $referenceName => $reference) {
                    if ($declarations[$referenceName] === self::TERMINATING_REFERENCE) {
                        $declarations[$declaration] = self::TERMINATING_REFERENCE;
                        continue(2);
                    }
                }
            }
        } while ($previousDeclarations != $declarations);

        // Return all references that contain anything more than the terminating symbol reference.
        $nonTerminatingDeclarationReferences = array_filter(
            $declarations,
            function (array $references) {
                return $references != self::TERMINATING_REFERENCE;
            }
        );

        // Resolve the reference to the real declaration objects before returning.
        return array_map(
            [$referenceGraph, 'getDeclarationByUniqueName'],
            array_keys($nonTerminatingDeclarationReferences)
        );
    }
}
