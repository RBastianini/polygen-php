<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;

/**
 * Matches terminating symbols.
 */
class TerminatingSymbolMatcher extends BaseMatcher
{
    const REGEX = "{^[a-z0-9'][a-zA-Z0-9']*$}D";

    /**
     * @return Token
     */
    public function doMatch()
    {
        $symbol = '';
        while ($this->matchesRegex($symbol . $this->peek())) {
            $lastChar = $this->read();
            if ($lastChar === null) {
                break;
            }
            $symbol .= $lastChar;
        };
        return strlen($symbol) ? Token::terminatingSymbol($symbol) : null;
    }
}
