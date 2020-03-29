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
     * @var FrequencyModifier[]
     */
    private $frequencyModifiers;

    /**
     * @var Sequence
     */
    private $sequence;

    public function __construct(array $modifiers, Sequence $sequence)
    {
        Assert::allIsInstanceOf($modifiers, FrequencyModifier::class);
        $this->frequencyModifiers = $modifiers;
        $this->sequence = $sequence;
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @return mixed
     */
    public function traverse(AbstractSyntaxWalker $walker)
    {
        return $walker->walkProduction($this);
    }

    /**
     * @return Sequence
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @return FrequencyModifier[]
     */
    public function getFrequencyModifiers()
    {
        return $this->frequencyModifiers;
    }
}
