<?php

namespace Polygen;

use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen document as read by the parser.
 */
class Document implements Node
{
    const INFORMATION = 'I';

    const START = 'S';

    /**
     * @var DeclarationInterface[]
     */
    private $declarations;

    /**
     * Document constructor.
     *
     * @param DeclarationInterface $declarations
     */
    public function __construct(array $declarations)
    {
        // Declarations appearing before other declarations are take precedence on those appearing later, so we flip the
        // declarations that we got and process them one by one and in the and we will have set only those that have not
        // been shadowed.
        $validDeclarations = [];
        foreach (array_reverse($declarations) as $declaration) {
            Assert::isInstanceOf($declaration, DeclarationInterface::class);
            $validDeclarations[$declaration->getName()] = $declaration;
        }
        $this->declarations = array_reverse($validDeclarations);
    }

    /**
     * @param string $name
     * @return DeclarationInterface
     */
    public function getDeclaration($name)
    {
        return $this->declarations[$name];
    }

    /**
     * @return DeclarationInterface[]
     */
    public function getDeclarations()
    {
        return array_values($this->declarations);
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkDocument($this);
    }

    /**
     * @param string $name
     * @param bool
     */
    public function isDeclared($name)
    {
        return array_key_exists($name, $this->declarations);
    }
}
