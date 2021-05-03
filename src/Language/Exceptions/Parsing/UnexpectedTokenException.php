<?php

namespace Polygen\Language\Exceptions\Parsing;

use Polygen\Language\Lexing\Position;
use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;

class UnexpectedTokenException extends \RuntimeException
{
    /**
     * UnexpectedTokenException constructor.
     *
     * @param Type[] $expectedTypes
     * @param Token|null $obtainedType
     */
    public function __construct(array $expectedTypes, $obtainedType, Position $position)
    {
        $obtainedType = $obtainedType ? : 'null';
        $expectedType = implode(', ', $expectedTypes);
        parent::__construct(
            "Expected token of type(s) {$expectedType}, {$obtainedType} found instead (Line {$position->getLine()} offset {$position->getColumn()})."
        );
    }
}
