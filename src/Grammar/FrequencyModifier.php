<?php

namespace Polygen\Grammar;

use Webmozart\Assert\Assert;

/**
 * Represents the number of plus or minus sign used to influence the frequency of terms during generation.
 */
class FrequencyModifier
{
    /**
     * @var int
     */
    private $decreaseFrequencyIntensity;

    /**
     * @var int
     */
    private $increaseFrequencyIntensity;

    /**
     * @param int $increaseFrequencyIntensity
     * @param int $decreaseFrequencyIntensity
     */
    public function __construct($increaseFrequencyIntensity, $decreaseFrequencyIntensity)
    {
        Assert::integer($increaseFrequencyIntensity);
        Assert::integer($decreaseFrequencyIntensity);
        Assert::greaterThanEq($increaseFrequencyIntensity, 0);
        Assert::greaterThanEq($decreaseFrequencyIntensity, 0);
        $this->increaseFrequencyIntensity = $increaseFrequencyIntensity;
        $this->decreaseFrequencyIntensity = $decreaseFrequencyIntensity;
    }

    /**
     * @return int
     */
    public function getNetFrequencyChange()
    {
        return $this->increaseFrequencyIntensity - $this->decreaseFrequencyIntensity;
    }

}
