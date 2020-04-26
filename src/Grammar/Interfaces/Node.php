<?php

namespace Polygen\Grammar\Interfaces;

use Polygen\Language\AbstractSyntaxWalker;

/**
 * Generic interface of all nodes in a Polygen Document.
 */
interface Node
{
    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null);
}
