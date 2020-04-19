<?php

namespace Polygen\Grammar\Unfoldable;

use Polygen\Grammar\FoldingModifier;
use Polygen\Grammar\Interfaces\Node;

/**
 * Unfoldable abstract parent class.
 */
abstract class Unfoldable implements Node
{
    /**
     * @var FoldingModifier|null
     */
    private $foldingModifier;

    public function __construct(FoldingModifier $foldingModifier = null)
    {
        $this->foldingModifier = $foldingModifier;
    }

    /**
     * @return FoldingModifier|null
     */
    public function getFoldingModifier()
    {
        return $this->foldingModifier;
    }
}
