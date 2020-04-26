<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\FrequencyModifiable;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen production.
 */
class Production implements Node, FrequencyModifiable
{
    /**
     * @var FrequencyModifier
     */
    private $frequencyModifier;

    /**
     * @var Sequence
     */
    private $sequence;

    public function __construct(Sequence $sequence, FrequencyModifier $frequencyModifier = null)
    {
        $this->frequencyModifier = $frequencyModifier ?: new FrequencyModifier(0, 0);
        $this->sequence = $sequence;
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkProduction($this, $context);
    }

    /**
     * @return Sequence
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @return FrequencyModifier
     */
    public function getFrequencyModifier()
    {
        Assert::notNull($this->frequencyModifier);
        return $this->frequencyModifier;
    }

    /**
     * @return static
     */
    public function withoutFrequencyModifier()
    {
        $clone = clone $this;
        $clone->frequencyModifier = New FrequencyModifier(0, 0);
        return $clone;
    }
}
