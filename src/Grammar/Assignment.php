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
     * @var ProductionCollection
     */
    private $productionCollection;

    /**
     * Assignment constructor.
     *
     * @param string $name
     */
    public function __construct($name, ProductionCollection $productions)
    {
        $this->name = $name;
        $this->productionCollection = $productions;
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
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkAssignment($this, $context);
    }

    /**
     * @deprecated
     * @return Production[]
     */
    public function getProductions()
    {
        return $this->productionCollection->getProductions();
    }

    /**
     * @return \Polygen\Grammar\ProductionCollection
     */
    public function getProductionSet()
    {
        return $this->productionCollection;
    }

    /**
     * Returns a new instance of this object with the same properties, but with the specified productions.
     *
     * @param Production[] $productions
     * @return static
     */
    public function withProductions(ProductionCollection $productions)
    {
        return new static($this->name, $productions);
    }
}
