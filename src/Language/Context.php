<?php

namespace Polygen\Language;

use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\Node;
use Webmozart\Assert\Assert;

/**
 * This object keeps track of the current context while traversing the tree to generate some output, to perform static
 * checks or to perform a syntax conversion.
 */
class Context
{
    /**
     * @var DeclarationInterface[]|Node[]
     */
    private $declarations = [];

    /**
     * Context constructor.
     *
     * @param DeclarationInterface[] $declarations
     */
    public function __construct(array $declarations = [])
    {
        Assert::allIsInstanceOf($declarations, DeclarationInterface::class);

        foreach ($declarations as $declaration) {
            $this->declarations[$declaration->getName()] = $declaration;
        }
    }

    /**
     * @param string $declaration
     * @return bool
     */
    public function isDeclared($declaration)
    {
        return array_key_exists($declaration, $this->declarations);
    }

    /**
     * @param DeclarationInterface[] $declarations
     * @return static
     */
    public function mergeDeclarations(array $declarations)
    {
        return new static(array_merge($this->declarations, $declarations));
    }

    /**
     * @param string $declarationName
     * @return DeclarationInterface
     */
    public function getDeclaration($declarationName)
    {
        Assert::true($this->isDeclared($declarationName));
        return $this->declarations[$declarationName];
    }

    public function isEmpty()
    {
        return empty($this->declarations);
    }
}
