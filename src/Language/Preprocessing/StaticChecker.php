<?php

namespace Polygen\Language\Preprocessing;

use Polygen\Language\Document;
use Polygen\Language\Errors\ErrorCollection;
use Polygen\Language\Preprocessing\Services\IdentifierFactory;
use Polygen\Language\Preprocessing\StaticCheck\InfiniteRecursionCheck;
use Polygen\Language\Preprocessing\StaticCheck\NonTerminatingSymbolDeclarationCheck;
use Polygen\Language\Preprocessing\StaticCheck\StartSymbolCheck;
use Polygen\Language\Preprocessing\StaticCheck\StaticCheckInterface;
use Webmozart\Assert\Assert;

/**
 * Walks down the document and checks that at any time an Non Terminating Symbol is reached, a definition or an
 * assignment are available to resolve it.
 */
class StaticChecker implements StaticCheckInterface
{
    /**
     * @var \Polygen\Language\Preprocessing\StaticCheck\StaticCheckInterface[]
     */
    private $staticChecks;

    /**
     * Factory method.
     * @param string $startSymbol Symbol from which generation should start
     * @return static
     */
    public static function get($startSymbol)
    {
        return new static([
                new StartSymbolCheck($startSymbol),
                new NonTerminatingSymbolDeclarationCheck(),
                new InfiniteRecursionCheck(new IdentifierFactory())
            ]);
    }

    /**
     * StaticChecker constructor.
     *
     * @param StaticCheckInterface[] $staticChecks
     */
    public function __construct(array $staticChecks)
    {
        Assert::allIsInstanceOf($staticChecks, StaticCheckInterface::class);
        $this->staticChecks = $staticChecks;
    }

    /**
     * @return \Polygen\Language\Errors\ErrorCollection
     */
    public function check(Document $document)
    {
        $errors = new ErrorCollection([]);
        foreach ($this->staticChecks as $staticCheck) {
            $errors = $errors->merge($staticCheck->check($document));
        }
        return $errors;
    }
}
