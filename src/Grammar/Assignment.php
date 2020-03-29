<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\DeclarationInterface;

/**
 * Represents a Polygen assignment.
 */
class Assignment implements DeclarationInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Production[]
     */
    private $productions;

    /**
     * Assignment constructor.
     *
     * @param string $name
     * @param Production[] $productions
     */
    public function __construct($name, array $productions)
    {
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
