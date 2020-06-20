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

    public function __sleep()
    {
        $this->foldingModifier = $this->foldingModifier ? $this->foldingModifier->getValue() : null;
        return ['foldingModifier'];
    }

    public function __wakeup()
    {
        if ($this->foldingModifier === null) {
            return;
        }
        $this->foldingModifier = FoldingModifier::fromValue($this->foldingModifier);
    }
}
