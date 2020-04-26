<?php

namespace Polygen\Language\Errors;

use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;
use Webmozart\Assert\Assert;

/**
 * Error raised when attempting to use a non terminating symbol for which a declaration is not available.
 */
class UndefinedNonTerminatingSymbol implements Error
{
    /**
     * @var \Polygen\Language\Token\Token
     */
    private $token;

    public function __construct(Token $token)
    {
        Assert::eq($token->getType(), Type::nonTerminatingSymbol());
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return "Undefined non terminating symbol '{$this->token->getValue()}'.";
    }
}
