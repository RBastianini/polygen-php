<?php

namespace Polygen\Language;

use Polygen\Grammar\Assignment;
use Polygen\Grammar\Definition;
use Polygen\Grammar\Interfaces\DeclarationInterface;
use Webmozart\Assert\Assert;

/**
 * This object keeps track of the current context while traversing the tree to generate some output, to perform static
 * checks or to perform a syntax conversion.
 */
class Context
{
    /**
     * @var \Polygen\Grammar\Definition[]
     */
    private $definitions;

    /**
     * @var \Polygen\Grammar\Assignment[]
     */
    private $assignments;

    /**
     * Context constructor.
     *
     * @param \Polygen\Grammar\Definition[] $definitions
     * @param \Polygen\Grammar\Assignment[] $assignments
     */
    public function __construct(array $definitions = [], array $assignments = [])
    {
        Assert::allIsInstanceOf($definitions, Definition::class);
        Assert::allIsInstanceOf($assignments, Assignment::class);

        $this->definitions = $this->indexDeclarations($definitions);
        $this->assignments = $this->indexDeclarations($assignments);
    }

    /**
     * @param string $declaration
     * @return bool
     */
    public function isDeclared($declaration)
    {
        return array_key_exists($declaration, $this->assignments)
            || array_key_exists($declaration, $this->definitions);
    }

    /**
     * @param \Polygen\Grammar\Definition[] $definitions
     */
    public function mergeDefinitions(array $definitions)
    {
        Assert::allIsInstanceOf($definitions, Definition::class);
        $clone = clone $this;
        $clone->definitions = $this->mergeDeclarations($this->definitions, $definitions);
        return $clone;
    }

    public function mergeAssignments(array $assignments)
    {
        Assert::allIsInstanceOf($assignments, Assignment::class);
        $clone = clone $this;
        $clone->assignments = $this->mergeDeclarations($this->assignments, $assignments);
        return $clone;
    }

    /**
     * @param DeclarationInterface[] $declarations
     * @return DeclarationInterface[] An array of declarations indexed by their name.
     */
    private function indexDeclarations(array $declarations)
    {
        $result = [];
        foreach ($declarations as $declaration) {
            $result[$declaration->getName()] = $declaration;
        }
        return $result;
    }

    private function mergeDeclarations(array $original, array $new)
    {
        return array_merge($original, $this->indexDeclarations($new));
    }
}
