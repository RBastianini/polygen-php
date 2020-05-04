<?php

namespace Polygen\Language;

use Polygen\Grammar\Interfaces\DeclarationInterface;

/**
 *
 */
interface ContextInterface
{
    /**
     * @param string $declaration
     * @return bool
     */
    public function isDeclared($declaration);

    /**
     * @param DeclarationInterface[] $declarations
     * @return static
     */
    public function mergeDeclarations(array $declarations);

    /**
     * @param string $declarationName
     * @return DeclarationInterface
     */
    public function getDeclaration($declarationName);

    /**
     * @return bool
     */
    public function isEmpty();
}
