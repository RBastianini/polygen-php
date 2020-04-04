<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\FrequencyModifiable;
use Polygen\Language\Token\Token;

/**
 * Represents a label with optional modifiers.
 */
class Label implements FrequencyModifiable
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var FrequencyModifier
     */
    private $frequencyModifier;

    /**
     * @param FrequencyModifier $modifiers
     */
    public function __construct(Token $label, FrequencyModifier $modifiers = null)
    {
        $this->frequencyModifier = $modifiers ?: new FrequencyModifier(0, 0);
        $this->label = $label->getValue();
    }

    /**
     * @return static
     */
    public function withoutFrequencyModifier()
    {
        $clone = clone $this;
        $clone->frequencyModifier = new FrequencyModifier(0, 0);
        return $clone;
    }

    /**
     * @return FrequencyModifier
     */
    public function getFrequencyModifier()
    {
        return $this->frequencyModifier;
    }
}
