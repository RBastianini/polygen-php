<?php

namespace Polygen\Language\Exceptions\Lexing;

class InvalidEscapeSequenceException extends \RuntimeException
{
    public function __construct($escape, $offset)
    {
        parent::__construct("Unknown escape sequence '$escape' at offset $offset.");
    }
}
