<?php

namespace Polygen\Grammar;

use Polygen\Utils\Enum;

/**
 * @method static FrequencyModifier plus()
 * @method static FrequencyModifier minus()
 */
class FrequencyModifier extends Enum
{
    const PLUS = 'PLUS';
    const MINUS = 'MINUS';
}
