<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen subproduction.
 */
class SubProduction implements Node
{
    /**
     * @var DeclarationInterface[]
     */
    private $declarationsOrAssignments;

    /**
     * @var Production[]
     */
    private $productions;

    /**
     * SubProduction constructor.
     *
     * @param DeclarationInterface[] $declarationsOrAssignments
     * @param Production[] $productions
     */
    public function __construct(array $declarationsOrAssignments, array $productions)
    {
        Assert::allImplementsInterface($declarationsOrAssignments, DeclarationInterface::class);
        Assert::allIsInstanceOf($productions, Production::class);
        $this->declarationsOrAssignments = $declarationsOrAssignments;
        $this->productions = $productions;
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @return mixed
     */
    public function traverse(AbstractSyntaxWalker $walker)
    {
        return $walker->walkSubProduction($this);
    }

    /**
     * @return DeclarationInterface[]
     */
    public function getDeclarationsOrAssignemnts()
    {
        return $this->declarationsOrAssignments;
    }

    /**
     * @return Production[]
     */
    public function getProductions()
    {
        return $this->productions;
    }

}
