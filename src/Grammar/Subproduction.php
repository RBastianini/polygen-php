<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\HasProductions;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen subproduction.
 */
class Subproduction implements HasProductions, Node
{
    /**
     * @var DeclarationInterface[]
     */
    private $declarations = [];

    /**
     * @var Production[]
     */
    private $productions;

    /**
     * Subproduction constructor.
     *
     * @param DeclarationInterface[] $declarations
     * @param Production[] $productions
     */
    public function __construct(array $declarations, array $productions)
    {
        $validDeclarations = [];
        foreach (array_reverse($declarations) as $declaration) {
            Assert::implementsInterface($declaration, DeclarationInterface::class);
            $validDeclarations[$declaration->getName()] = $declaration;
        }
        $this->declarations = array_reverse($validDeclarations);
        Assert::allIsInstanceOf($productions, Production::class);
        $this->productions = $productions;
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkSubproduction($this, $context);
    }

    /**
     * @return DeclarationInterface[]
     */
    public function getDeclarations()
    {
        return array_values($this->declarations);
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
