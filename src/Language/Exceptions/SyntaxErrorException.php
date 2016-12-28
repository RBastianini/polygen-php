<?php

namespace Polygen\Language\Exceptions;

class SyntaxErrorException extends \RuntimeException
{

    public function __construct($offset)
    {
        parent::__construct("Syntax error at offset $offset.");
    }
}
