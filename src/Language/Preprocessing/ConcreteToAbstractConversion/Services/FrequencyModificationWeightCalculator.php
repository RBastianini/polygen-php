<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services;

use Polygen\Grammar\Interfaces\FrequencyModifiable;
use Webmozart\Assert\Assert;

/**
 * Given an array of items whose generation frequency can be influenced, computes the weight of such items.
 * @see FrequencyModifiedSelectionLabelToDotLabelConverter for details on the implemented algorithm,
 */
class FrequencyModificationWeightCalculator
{
    /**
     * @param FrequencyModifiable[] $frequencyModifiables
     * @return int[]
     */
    public function getFrequencyModificationWeightByPosition(array $frequencyModifiables)
    {
        $minimumFrequencyModificationWeight = PHP_INT_MAX;
        $frequencyModificationWeightByPosition = [];
        foreach ($frequencyModifiables as $frequencyModifiable) {
            Assert::isInstanceOf($frequencyModifiable, FrequencyModifiable::class);
            $frequencyModificationWeight = $frequencyModifiable->getFrequencyModifier()->getNetFrequencyChange();
            $frequencyModificationWeightByPosition[] = $frequencyModificationWeight;
            if ($frequencyModificationWeight < $minimumFrequencyModificationWeight) {
                $minimumFrequencyModificationWeight = $frequencyModificationWeight;
            }
        }
        return array_map(
            function ($frequencyModificationWeight) use ($minimumFrequencyModificationWeight) {
                return 1 + $frequencyModificationWeight - $minimumFrequencyModificationWeight;
            },
            $frequencyModificationWeightByPosition
        );
    }
}
