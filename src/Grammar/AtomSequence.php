<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * AtomSequence Polygen node.
 */
class AtomSequence implements Node
{
    /**
     * @var Atom[]
     */
    private $atoms;

    public function __construct(array $atoms)
    {
        Assert::allIsInstanceOf($atoms, Atom::class);
        $this->atoms = array_values($atoms);
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkAtomSequence($this, $context);
    }

    /***
     * @return Atom[]
     */
    public function getAtoms()
    {
        return $this->atoms;
    }
}
