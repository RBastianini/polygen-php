<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\Labelable;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen Sequence.
 */
class Sequence
{
    /**
     * @var Label
     */
    private $label;

    /**
     * @var Labelable[]
     */
    private $atoms;

    /**
     * Sequence constructor.
     *
     * @param Labelable[] $labelables
     */
    public function __construct(array $labelables, Label $label = null)
    {
        Assert::allImplementsInterface($labelables, Labelable::class);
        $this->label = $label;
        $this->atoms = $labelables;
    }
}
