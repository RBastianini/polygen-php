<?php

namespace Polygen\Language\Exceptions;

use Polygen\Language\Lexing\Position;

class SyntaxErrorException extends \RuntimeException
{

    /**
     * @return static
     */
    public static function atPosition(Position $position)
    {
        return new static($position, 'Syntax error');
    }

    /**
     * @return static
     */
    public static function unterminatedComment(Position $position)
    {
        return new static($position, 'Unterminated comment');
    }

    /**
     * @return static
     */
    public static function unterminatedString(Position $position)
    {
        return new static($position, 'Unterminated string');
    }

    /**
     * @param string $escapeSequence
     * @return static
     */
    public static function invalidEscapeSequence($escapeSequence, Position $position)
    {
        return new static($position, "Invalid escape sequence '{$escapeSequence}'");
    }

    /**
     * @param string $issue
     */
    public function __construct(Position $position, $issue)
    {
        parent::__construct("$issue at line {$position->getLine()} and column {$position->getColumn()}.");
    }
}
