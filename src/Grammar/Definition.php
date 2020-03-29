<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Definition Polygen node
 */
class Definition implements DeclarationInterface, Node
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
     * @param string $name
     * @param Production[] $productions
     */
    public function __construct($name, array $productions)
    {
        Assert::allIsInstanceOf($productions, Production::class);
        Assert::string($name);
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
        return $walker->walkDefinition($this);
    }

    /**
     * @return Production[]
     */
    public function getProductions()
    {
        return $this->productions;
    }
}
