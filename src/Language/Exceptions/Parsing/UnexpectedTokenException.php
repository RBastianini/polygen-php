<?php

namespace Polygen\Language\Exceptions\Parsing;

use Polygen\Language\Token\Token;

/**
 *
 */
class UnexpectedTokenException extends \RuntimeException
{
    /**
     * UnexpectedTokenException constructor.
     *
     * @param array $expectedTypes
     * @param Token|null $obtainedType
     */
    public function __construct(array $expectedTypes, $obtainedType)
    {
        $obtainedType = $obtainedType ? : 'null';
        $expectedType = implode(', ', $expectedTypes);
        parent::__construct(
            "Expected token of type {$expectedType}, {$obtainedType} found instead."
        );
    }
}
