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
     * @var ProductionCollection
     */
    private $productions;

    /**
     * @param string $name
     */
    public function __construct($name, ProductionCollection $productions)
    {
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
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkDefinition($this, $context);
    }

    /**
     * @return ProductionCollection
     */
    public function getProductionSet()
    {
        return $this->productions;
    }

    /**
     * Returns a new instance of this object with the same properties, but with the specified productions.
     *
     * @return static
     */
    public function withProductions(ProductionCollection $productions)
    {
        return new static($this->name, $productions);
    }
}
