<?php

namespace Polygen\Grammar\Interfaces;

/**
 * Common interface for Polygen declarations.
 */
interface DeclarationInterface extends HasProductions
{
    /**
     * @return string
     */
    public function getName();
}
