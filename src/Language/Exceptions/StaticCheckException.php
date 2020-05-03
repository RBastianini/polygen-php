<?php

namespace Polygen\Language\Exceptions;

use Polygen\Language\Errors\ErrorCollection;

class StaticCheckException extends \RuntimeException
{
    /**
     * @var \Polygen\Language\Errors\ErrorCollection
     */
    private $errorCollection;

    public function __construct(ErrorCollection $errorCollection)
    {
        parent::__construct('Static Syntax check failed');
        $this->errorCollection = $errorCollection;
    }

    /**
     * @return ErrorCollection
     */
    public function getErrors()
    {
        return $this->errorCollection;
    }
}
