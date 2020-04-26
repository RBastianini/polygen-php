<?php

namespace Polygen\Language\Errors;

/**
 * Base interface for error messages.
 */
interface Error
{
    /**
     * @return string
     */
    public function getMessage();
}
