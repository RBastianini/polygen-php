<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Interfaces\HasProductions;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\FrequencyModificationWeightCalculator;

/**
 * Converts a production with sequences having modified frequency into an equivalent production with non-frequency
 * modified sequences.
 */
class FrequencyModifierProductionConverter implements ConverterInterface
{

    /**
     * @var \Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\FrequencyModificationWeightCalculator
     */
    private $frequencyModificationWeightCalculator;

    public function __construct(FrequencyModificationWeightCalculator $frequencyModificationWeightCalculator)
    {
        $this->frequencyModificationWeightCalculator = $frequencyModificationWeightCalculator;
    }

    /**
     * The lowest the number, the highest the priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return 2;
    }

    /**
     * @param HasProductions $node
     * @return Node
     */
    public function convert(Node $node)
    {
        $frequencyWeightByPosition = $this->frequencyModificationWeightCalculator->getFrequencyModificationWeightByPosition(
            $node->getProductions()
        );

        $productions = [];
        foreach ($node->getProductions() as $productionIndex => $production) {
            $productions = array_merge(
                $productions,
                array_fill(
                    0,
                    $frequencyWeightByPosition[$productionIndex],
                    $production->withoutFrequencyModifier()
                )
            );
        }
        return $node->withProductions($productions);
    }

    /**
     * @param Node $node
     * @return bool
     */
    public function canConvert(Node $node)
    {
        return $node instanceof HasProductions;
    }
}
