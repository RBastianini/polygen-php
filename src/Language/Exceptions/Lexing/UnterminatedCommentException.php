<?php

namespace Polygen\Language\Exceptions\Lexing;

use Polygen\Language\Exceptions\SyntaxErrorException;
use Polygen\Language\Lexing\Position;

class UnterminatedCommentException extends SyntaxErrorException
{
    public static function atPosition(Position $position)
    {
        return new static($position, 'Unterminated comment');
    }
}
