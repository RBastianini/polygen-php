<?php

namespace Polygen\Language\Preprocessing\Services;

/**
 * Generates an unique identifier with the specified prefix.
 *
 * Actually only useful for controlling name generation for testing.
 */
class IdentifierFactory
{
    /**
     * @param string $prefix
     * @return string
     */
    public function getId($prefix)
    {
        return uniqid($prefix);
    }
}
