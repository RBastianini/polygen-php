<?php

namespace Polygen\Grammar\Interfaces;

/**
 * Interfaces for nodes holding declarations.
 */
interface HasDeclarations
{
    /**
     * @return DeclarationInterface[]
     */
    public function getDeclarations();
}
