<?php

namespace Polygen\Language\Errors;

use Polygen\Language\Document;

/**
 * Error for grammars missing the start symbol.
 */
class NoStartSymbol implements Error
{
    /**
     * @return string
     */
    public function getMessage()
    {
        return sprintf("Undefined start symbol: '%s'.", Document::START);
    }
}
