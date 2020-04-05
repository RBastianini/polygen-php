<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen assignment.
 */
class Assignment implements DeclarationInterface, Node
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Production[]
     */
    private $productions;

    /**
     * Assignment constructor.
     *
     * @param string $name
     * @param Production[] $productions
     */
    public function __construct($name, array $productions)
    {
        $this->name = $name;
        $this->productions = $productions;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @return mixed
     */
    public function traverse(AbstractSyntaxWalker $walker)
    {
        return $walker->walkAssignment($this);
    }

    /**
     * @return Production[]
     */
    public function getProductions()
    {
        return $this->productions;
    }

    /**
     * Returns a new instance of this object with the same properties, but with the specified productions.
     *
     * @param Production[] $productions
     * @return static
     */
    public function withProductions(array $productions)
    {
        Assert::allIsInstanceOf($productions, Production::class);
        $clone = clone $this;
        $clone->productions = $productions;
        return $clone;
    }
}
