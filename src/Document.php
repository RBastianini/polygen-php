<?php

namespace Polygen;

use Polygen\Grammar\Assignment;
use Polygen\Grammar\Definition;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen document as read by the parser.
 */
class Document implements Node
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

    /**
     * @return Definition[]
     */
    public function getDefinitions()
    {
        return array_values($this->definitions);
    }

    /**
     * @return Assignment[]
     */
    public function getAssignments()
    {
        return array_values($this->assignments);
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkDocument($this);
    }

    /**
     * @param string $name
     * @param bool
     */
    public function isDeclared($name)
    {
        return array_key_exists($name, $this->definitions)
            || array_key_exists($name, $this->assignments);
    }
}
