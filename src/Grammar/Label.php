<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\FrequencyModifiable;
use Polygen\Language\Token\Token;
use Webmozart\Assert\Assert;

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
     * @param string $label
     * @param FrequencyModifier $modifiers
     */
    public function __construct($label, FrequencyModifier $modifiers = null)
    {
        Assert::string($label);
        Assert::false($label === '', "A label can't be an empty string.");
        $this->frequencyModifier = $modifiers ?: new FrequencyModifier(0, 0);
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->label;
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
