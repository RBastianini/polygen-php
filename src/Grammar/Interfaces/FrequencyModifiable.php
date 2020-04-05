<?php

namespace Polygen\Grammar\Interfaces;

use Polygen\Grammar\FrequencyModifier;

/**
 * Intraface for objects whose frequency can be modified.
 */
interface FrequencyModifiable
{
    /**
     * @return FrequencyModifier
     */
    public function getFrequencyModifier();

    /**
     * Returns a new instance of the same object with the same properties, except for a zeroed-out frequency modifier.
     *
     * @return static
     */
    public function withoutFrequencyModifier();
}
