<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\Labelable;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * AtomSequence Polygen node.
 */
class AtomSequence implements Node
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

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @return mixed
     */
    public function traverse(AbstractSyntaxWalker $walker)
    {
        return $walker->walkAtomSequence($this);
    }

    /***
     * @return Labelable[]
     */
    public function getAtoms()
    {
        return $this->atoms;
    }
}
