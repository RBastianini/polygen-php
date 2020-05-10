<?php

namespace Polygen\Utils;

use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\Node;
use Webmozart\Assert\Assert;

/**
 * This object holds a collection of declarations.
 */
class DeclarationCollection
{
    /**
     * @var DeclarationInterface[]|Node[]
     */
    private $declarations = [];

    /**
     * DeclarationCollection constructor.
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
