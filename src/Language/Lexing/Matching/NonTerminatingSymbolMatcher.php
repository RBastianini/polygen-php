<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;

/**
 * Matches non terminating symbols.
 */
class NonTerminatingSymbolMatcher extends BaseMatcher
{
    const REGEX = '{^[A-Z][A-Za-z0-9]*$}D';

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
        }
        return strlen($symbol) ? Token::nonTerminatingSymbol($symbol) : null;
    }
}
