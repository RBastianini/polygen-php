<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Unfoldable\Unfoldable;
use Polygen\Grammar\Unfoldable\SubproductionUnfoldableType;
use Polygen\Language\AbstractSyntaxWalker;

/**
 * Represents a Polygen subproduction unfoldable.
 */
class SubproductionUnfoldable extends Unfoldable
{
    /**
     * @var Subproduction
     */
    private $subproduction;

    /**
     * @var SubproductionUnfoldableType
     */
    protected $type;

    /**
     * @internal
     */
    public function __construct(
        Subproduction $subproduction,
        SubproductionUnfoldableType $type,
        FoldingModifier $foldingModifier = null
    ) {
        parent::__construct($foldingModifier);
        $this->type = $type;
        $this->subproduction = $subproduction;
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkSubproductionUnfoldable($this, $context);
    }

    /**
     * @return SubproductionUnfoldableType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Subproduction
     */
    public function getSubproduction()
    {
        return $this->subproduction;
    }

    public function __sleep()
    {
        $this->type = $this->type->getValue();
        return ['subproduction', 'type'];
    }

    public function __wakeup()
    {
        $this->type = SubproductionUnfoldableType::fromValue($this->type);
    }
}
