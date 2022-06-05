<?php

namespace Polygen\Language\Preprocessing\StaticCheck\InfiniteRecursionCheck;

use Polygen\Grammar\Interfaces\DeclarationInterface;
use Webmozart\Assert\Assert;

/**
 * This class holds a graph of referenced declarations for each declaration, in the form of adjacency list.
 */
class ReferenceGraph
{
    const TERMINATING_SYMBOL = '#';

    /**
     * An array of declarations referenced by each other declaration.
     *
     * @var string[][]
     */
    private $referencedDeclarationsByUniqueName = [];

    /**
     * An array of declarations, keyed by their unique name.
     * @var DeclarationInterface[]
     */
    private $declarationsByUniqueName = [];

    /**
     * Registers into the reference graphs the references passed as being produced by the passed declaration, in the
     * passed scope.
     *
     * @param string[] $referencedDeclarations
     */
    public function addReferences(
        DeclarationInterface $declaration,
        DeclarationsContext $existingDeclarations,
        array $referencedDeclarations
    ) {
        Assert::true($existingDeclarations->isDeclared($declaration->getName()));
        $uniqueName = $existingDeclarations->getUniqueName($declaration->getName());
        Assert::keyNotExists($this->declarationsByUniqueName, $uniqueName);

        $this->declarationsByUniqueName[$uniqueName] = $declaration;
        $this->referencedDeclarationsByUniqueName[$uniqueName] = $referencedDeclarations;
    }

    /**
     * @return string[][]
     */
    public function getReferencedDeclarationsByUniqueName()
    {
        return $this->referencedDeclarationsByUniqueName;
    }

    /**
     * @return \Polygen\Grammar\Interfaces\DeclarationInterface[]
     */
    public function getDeclarationsByUniqueName()
    {
        return $this->declarationsByUniqueName;
    }

    /**
     * @param string $nonTerminatingDeclarationReference
     * @return DeclarationInterface
     */
    public function getDeclarationByUniqueName($nonTerminatingDeclarationReference)
    {
        Assert::keyExists($this->declarationsByUniqueName, $nonTerminatingDeclarationReference);
        return $this->declarationsByUniqueName[$nonTerminatingDeclarationReference];
    }
}
