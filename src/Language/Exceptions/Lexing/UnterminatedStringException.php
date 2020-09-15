<?php

namespace Polygen\Language\Exceptions\Lexing;

use Polygen\Language\Exceptions\SyntaxErrorException;
use Polygen\Language\Lexing\Position;

class UnterminatedStringException extends SyntaxErrorException
{
    public function __construct(Position $position)
    {
        parent::__construct($position, 'Unterminated string');
    }
}
