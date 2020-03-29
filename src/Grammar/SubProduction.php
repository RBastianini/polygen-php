<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\DeclarationInterface;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen subproduction.
 */
class SubProduction
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

}
