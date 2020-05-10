<?php

namespace Polygen\Language\Errors;

use Polygen\Language\Document;

/**
 * Error for grammars missing the start symbol.
 */
class NoStartSymbol implements Error
{
    /**
     * @var string
     */
    private $startSymbol;

    /**
     * @param string $startSymbol
     */
    public function __construct($startSymbol)
    {
        $this->startSymbol = $startSymbol;
    }
    /**
     * @return string
     */
    public function getMessage()
    {
        return sprintf("Undefined start symbol: '{$this->startSymbol}'.");
    }
}
