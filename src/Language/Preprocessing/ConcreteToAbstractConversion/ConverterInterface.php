<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Interfaces\Node;

/**
 * Interface for converters to use for the concrete to abstract syntax conversion.
 */
interface ConverterInterface
{
    /**
     * The lowest the number, the highest the priority.
     *
     * @return int
     */
    public function getPriority();

    /**
     * Converts the node (and just it) and returns the converted node.
     *
     * @param Node $node
     * @return Node
     */
    public function convert(Node $node);

    /**
     * Returns true if the converter can operate on the passed node.
     *
     * @param Node $node
     * @return bool
     */
    public function canConvert(Node $node);
}
