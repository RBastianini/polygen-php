<?php

namespace Polygen\Language\Errors;

use Polygen\Grammar\Interfaces\DeclarationInterface;

/**
 *
 */
class InfiniteRecursion implements Error
{
    /**
     * @var \Polygen\Grammar\Interfaces\DeclarationInterface
     */
    private $declaration;

    public function __construct(DeclarationInterface $declaration)
    {
        $this->declaration = $declaration;
    }

    public function getMessage()
    {
        return "Infinite recursion detected for ndeclaration '{$this->declaration->getName()}'.";
    }
}
