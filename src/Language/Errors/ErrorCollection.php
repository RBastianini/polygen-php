<?php

namespace Polygen\Language\Errors;

use Webmozart\Assert\Assert;

/**
 * An immutable collection of error messages.
 */
class ErrorCollection
{
    /**
     * @var \Polygen\Language\Errors\Error[]
     */
    private $errors;

    public function __construct(array $errors = [])
    {
        Assert::allImplementsInterface($errors, Error::class);
        $this->errors = $errors;
    }

    /**
     * @return \Polygen\Language\Errors\Error[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Merges in the passed error collection, returning a new one.
     *
     * @param \Polygen\Language\Errors\ErrorCollection $errorCollection
     * @return $this
     */
    public function merge(ErrorCollection $errorCollection)
    {
        return new static(array_merge($this->errors, $errorCollection->getErrors()));
    }
}
