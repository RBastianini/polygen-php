<?php

namespace Polygen\Language\Preprocessing\StaticCheck;

use Polygen\Language\Document;

/**
 * Interface for checks to run on Polygen documents.
 */
interface StaticCheckInterface
{
    /**
     * @return \Polygen\Language\Errors\ErrorCollection
     */
    public function check(Document $document);
}
