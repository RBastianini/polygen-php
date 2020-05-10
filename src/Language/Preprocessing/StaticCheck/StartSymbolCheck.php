<?php

namespace Polygen\Language\Preprocessing\StaticCheck;

use Polygen\Language\Document;
use Polygen\Language\Errors\ErrorCollection;
use Polygen\Language\Errors\NoStartSymbol;

/**
 * Checks a document in search of the start symbol.
 */
class StartSymbolCheck implements StaticCheckInterface
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
     * @param Document $document
     * @return ErrorCollection
     */
    public function check(Document $document)
    {
        $errors = [];
        if (!$document->isDeclared($this->startSymbol)) {
            $errors[] = new NoStartSymbol($this->startSymbol);
        }

        return new ErrorCollection($errors);
    }


}
