<?php

namespace Polygen\Grammar;

use Polygen\Language\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Represents a label with optional modifiers.
 */
class Label
{
    /**
     * @var FrequencyModifier[]
     */
    private $modifiers;

    /**
     * @var string
     */
    private $label;

    /**
     * @param \Polygen\Grammar\FrequencyModifier $modifiers
     */
    public function __construct(Token $label, array $modifiers = [])
    {
        $modifierType = null;
        foreach ($modifiers as $modifier) {
            Assert::isInstanceOf($modifiers, FrequencyModifier::class);
            $modifierType = $modifierType ?: $modifier;
            Assert::eq($modifier, $modifierType, 'Cannot associate mixed modifiers to a label.');
        }
        $this->modifiers = $modifiers;
        $this->label = $label->getValue();
    }
}
