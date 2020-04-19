<?php

namespace Tests\Unit\Language\Preprocessing\ConcreteToAbstractConversion\Services;

use Mockery;
use Polygen\Grammar\FrequencyModifier;
use Polygen\Grammar\Interfaces\FrequencyModifiable;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\FrequencyModificationWeightCalculator;
use Tests\TestCase;

class FrequencyModificationWeightCalculatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider provider_weights
     * @param FrequencyModifiable[] $modifiables
     * @param int[] $expectedWeights
     */
    public function it_returns_correct_weights($modifiables, $expectedWeights)
    {
        $subject = new FrequencyModificationWeightCalculator();

        $result = $subject->getFrequencyModificationWeightByPosition($modifiables);

        $this->assertEquals($expectedWeights, $result);
    }

    public function provider_weights()
    {
        return [
            // Returns one for all weights if there are no modifiers
            [
                // Modifiables
                [
                    $this->given_a_weight_modifiable(0, 0),
                    $this->given_a_weight_modifiable(0, 0),
                    $this->given_a_weight_modifiable(0, 0),
                ],
                // Weights
                [1, 1, 1]
            ],
            // Returns one more if only one modifier had one plus sign
            [
                // Modifiables
                [
                    $this->given_a_weight_modifiable(0, 0),
                    $this->given_a_weight_modifiable(1, 0),
                    $this->given_a_weight_modifiable(0, 0),
                ],
                // Weights
                [1, 2, 1]
            ],
            // Returns all ones if frequency modification signs cancel out
            [
                // Modifiables
                [
                    $this->given_a_weight_modifiable(0, 0),
                    $this->given_a_weight_modifiable(1, 1),
                    $this->given_a_weight_modifiable(0, 0),
                ],
                // Weights
                [1, 1, 1]
            ],
            // Returns one less for a weight if it has one minus sign
            [
                // Modifiables
                [
                    $this->given_a_weight_modifiable(0, 0),
                    $this->given_a_weight_modifiable(0, 1),
                    $this->given_a_weight_modifiable(0, 0),
                ],
                // Weights
                [2, 1, 2]
            ],
            // Plus and minus sign can stack up
            [
                // Modifiables
                [
                    $this->given_a_weight_modifiable(0, 0),
                    $this->given_a_weight_modifiable(1, 0),
                    $this->given_a_weight_modifiable(2, 0),
                    $this->given_a_weight_modifiable(0, 0),
                    $this->given_a_weight_modifiable(0, 1),
                    $this->given_a_weight_modifiable(0, 2),
                ],
                // Weights
                [3, 4, 5, 3, 2, 1]
            ],
        ];
    }

    private function given_a_weight_modifiable($plusCount, $minusCount)
    {
        return Mockery::mock(FrequencyModifiable::class)->shouldReceive('getFrequencyModifier')
            ->andReturn(
                new FrequencyModifier($plusCount, $minusCount)
            )->getMock();
    }
}
