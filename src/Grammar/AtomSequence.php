<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\Labelable;
use Webmozart\Assert\Assert;

/**
 *
 */
class AtomSequence
{
    /**
     * @var Labelable[]
     */
    private $atoms;

    public function __construct(array $atoms)
    {
        Assert::allImplementsInterface($atoms, Labelable::class);
        $this->atoms = $atoms;
    }

    /***
     * @return Labelable[]
     */
    public function getAtoms()
    {
        return $this->atoms;
    }
}
