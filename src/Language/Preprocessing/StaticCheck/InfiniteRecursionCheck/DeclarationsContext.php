<?php

namespace Polygen\Language\Preprocessing\StaticCheck\InfiniteRecursionCheck;

use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Language\Preprocessing\Services\IdentifierFactory;
use Polygen\Utils\DeclarationCollection;
use Webmozart\Assert\Assert;

/**
 * A special kind of declaration context that holds references to containing declaration contexts, thus being able to
 * climb back the chain until the actual context where a declaration actually belongs.
 * We use this trick to disambiguate between overridden declarations in narrower contexts.
 */
class DeclarationsContext
{
    const ROOT_CONTEXT = 'root';

    /**
     * @var static|null
     */
    protected $parentContext;

    /**
     * @var DeclarationCollection
     */
    private $currentDeclarations;

    /**
     * @var string
     */
    private $contextName;

    public static function root(DeclarationCollection $context)
    {
        return new static(self::ROOT_CONTEXT, null, $context);
    }

    /**
     * @param string $contextName
     * @param DeclarationsContext|null $parentContext
     * @param DeclarationCollection|null $currentContext
     */
    private function __construct(
        $contextName,
        DeclarationsContext $parentContext = null,
        DeclarationCollection $currentContext = null
    ) {
        $this->parentContext = $parentContext;
        $this->currentDeclarations = $currentContext;
        $this->contextName = $contextName;
    }

    /**
     * Checks if a declaration is valid in the current context. A declaration is valid if it was inherited from a parent
     * scope or if it was declared in the current scope.
     *
     * @param string $declarationName
     * @return bool
     */
    public function isDeclared($declarationName)
    {
        if ($this->parentContext === null) {
            return $this->currentDeclarations->isDeclared($declarationName);
        }

        return $this->currentDeclarations->isDeclared($declarationName)
            || $this->parentContext->isDeclared($declarationName);
    }

    /**
     * Checks whether a declaration is actually added in the current scope.
     *
     * @param string $declarationName
     * @return bool
     */
    public function ownsDeclaration($declarationName)
    {
        return $this->currentDeclarations->isDeclared($declarationName);
    }

    /**
     * Creates a new narrower scope, by adding the declarations present in the passed context.
     *
     * @param DeclarationCollection $context
     * @return $this
     */
    public function addDeclarations(DeclarationCollection $context, IdentifierFactory $identifierFactory = null)
    {
        if ($context->isEmpty()) {
            return $this;
        }
        $identifierFactory = $identifierFactory ?: new IdentifierFactory();
        return new static($identifierFactory->getId('context'), $this, $context);
    }

    /**
     * Returns a declaration given its name.
     *
     * @param string $atom
     * @return DeclarationInterface
     */
    public function getDeclaration($declarationName)
    {
        if ($this->ownsDeclaration($declarationName)) {
            return $this->currentDeclarations->getDeclaration($declarationName);
        } else if ($this->parentContext !== null) {
           return $this->parentContext->getDeclaration($declarationName);
        }
        return null;
    }

    /**
     * Returns the unique declaration name.
     * This is useful to disambiguate between multiple declarations with the same name in contained scopes.
     *
     * @param string $declarationName
     * @return string
     */
    public function getUniqueName($declarationName)
    {
        Assert::true($this->isDeclared($declarationName), "Undeclared $declarationName");
        return $this->ownsDeclaration($declarationName)
            ? "{$this->contextName}>{$declarationName}"
            : $this->parentContext->getUniqueName($declarationName);
    }
}
