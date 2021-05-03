<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;

/**
 * Matches non terminating symbols.
 */
class NonTerminatingSymbolMatcher implements MatcherInterface
{
    use RegexMatcherTrait;

    const REGEX = '{^[A-Z][A-Za-z0-9]*$}D';

    /**
     * @return MatchedToken|null
     */
    public function match(MatcherInput $streamWrapper)
    {
        $symbol = '';
        while ($this->matchesRegex($symbol . $streamWrapper->peek())) {
            $lastChar = $streamWrapper->read();
            if ($lastChar === null) {
                break;
            }
            $symbol .= $lastChar;
        }
        return strlen($symbol)
            ? new MatchedToken(Token::nonTerminatingSymbol($symbol), $streamWrapper->getPosition())
            : null;
    }
}
