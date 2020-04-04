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
}
