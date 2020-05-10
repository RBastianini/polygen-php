<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Interfaces\Node;
use Polygen\Utils\DeclarationCollection;

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
     * @return Node
     */
    public function convert(Node $node, DeclarationCollection $context);

    /**
     * Returns true if the converter can operate on the passed node.
     *
     * @return bool
     */
    public function canConvert(Node $node);
}
