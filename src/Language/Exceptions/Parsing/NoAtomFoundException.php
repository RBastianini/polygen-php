<?php

namespace Polygen\Language\Exceptions\Parsing;

use Polygen\Language\Lexing\Matching\MatchedToken;

class NoAtomFoundException extends \RuntimeException
{

    public function __construct(MatchedToken $matchedToken)
    {
        parent::__construct(
            "Expected to match atom sequence, but no atom found. {$matchedToken->getToken()} found instead "
            . "(Line {$matchedToken->getPosition()->getLine()} offset {$matchedToken->getPosition()->getColumn()})."
        );
    }
}
