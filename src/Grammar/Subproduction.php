<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\HasDeclarations;
use Polygen\Grammar\Interfaces\HasProductions;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen subproduction.
 */
class Subproduction implements HasDeclarations, HasProductions, Node
{
    /**
     * @var DeclarationInterface[]
     */
    private $declarations;

    /**
     * @var ProductionCollection
     */
    private $productionSet;

    /**
     * Subproduction constructor.
     *
     * @param DeclarationInterface[] $declarations
     */
    public function __construct(array $declarations, ProductionCollection $productions)
    {
        $validDeclarations = [];
        foreach (array_reverse($declarations) as $declaration) {
            Assert::implementsInterface($declaration, DeclarationInterface::class);
            $validDeclarations[$declaration->getName()] = $declaration;
        }
        $this->declarations = array_reverse($validDeclarations);
        $this->productionSet = $productions;
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
     * @deprecated
     * @return Production[]
     */
    public function getProductions()
    {
        return $this->productionSet->getProductions();
    }

    /**
     * @return ProductionCollection
     */
    public function getProductionSet()
    {
        return $this->productionSet;
    }

    /**
     * Returns a new instance of this object with the same properties, but with the specified productions.
     *
     * @return static
     */
    public function withProductions(ProductionCollection $productions)
    {
        return new static($this->declarations, $productions);
    }
}
