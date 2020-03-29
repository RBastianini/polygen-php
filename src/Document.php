<?php

namespace Polygen;

use Polygen\Grammar\Assignment;
use Polygen\Grammar\Definition;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen document as read by the parser.
 */
class Document
{
    const INFORMATION = 'I';

    const START = 'S';

    /**
     * @var Assignment[]
     */
    private $assignments = [];

    /**
     * @var Definition[]
     */
    private $definitions = [];

    /**
     * Document constructor.
     *
     * @param Definition[] $definitions
     * @param Assignment[] $assignments
     */
    public function __construct(array $definitions, array $assignments)
    {
        foreach ($definitions as $definition) {
            Assert::keyNotExists($this->definitions, $definition->getName(), "Multiple definitions named {$definition->getName()} found.");
            $this->definitions[$definition->getName()] = $definition;
        }
        foreach ($assignments as $assignment) {
            Assert::keyNotExists($this->assignments, $assignment->getName(), "Multiple assignments named {$assignment->getName()} found.");
            $this->assignments[$assignment->getName()] = $assignment;
        }
    }

    /**
     * @param string $name
     * @return \Polygen\Grammar\Definition
     */
    public function getDefinition($name)
    {
        return $this->definitions[$name];
    }

    /**
     * @param string $name
     * @return \Polygen\Grammar\Assignment
     */
    public function getAssignment($name)
    {
        return $this->assignments[$name];
    }
}
