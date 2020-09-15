<?php

namespace Polygen\Language\Exceptions\Lexing;

use Polygen\Language\Exceptions\SyntaxErrorException;
use Polygen\Language\Lexing\Position;

class InvalidEscapeSequenceException extends SyntaxErrorException
{
    /**
     * @param string $escape
     */
    public function __construct($escape, Position $position)
    {
        parent::__construct($position, "Unknown escape sequence '$escape'");
    }
}
