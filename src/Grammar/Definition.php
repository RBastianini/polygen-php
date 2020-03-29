<?php

namespace Polygen\Grammar;

use Webmozart\Assert\Assert;

/**
 * Class Definition
 *
 * @package Polygen\Grammar
 */
class Definition
{
    private $name;

    private $productions;

    /**
     * @param string $name
     * @param Production[] $productions
     */
    public function __construct($name, array $productions)
    {
        Assert::allIsInstanceOf($productions, Production::class);
        $this->name = $name;
        $this->productions = $productions;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
