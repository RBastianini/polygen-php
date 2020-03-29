<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;

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
}
