<?php

namespace Polygen\Grammar;

use Webmozart\Assert\Assert;

/**
 * Represents a Polygen production.
 */
class Production
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
}
