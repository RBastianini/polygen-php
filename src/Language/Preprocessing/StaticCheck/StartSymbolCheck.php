<?php

namespace Polygen\Language\Preprocessing\StaticCheck;

use Polygen\Document;
use Polygen\Language\Errors\ErrorCollection;
use Polygen\Language\Errors\NoStartSymbol;

/**
 * Checks a document in search of the start symbol.
 */
class StartSymbolCheck implements StaticCheckInterface
{
    /**
     * @param \Polygen\Document $document
     * @return ErrorCollection
     */
    public function check(Document $document)
    {
        $errors = [];
        if (!$document->isDeclared(Document::START)) {
            $errors[] = new NoStartSymbol();
        }

        return new ErrorCollection($errors);
    }


}
