<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;

/**
 * Matches terminating symbols.
 */
class TerminatingSymbolMatcher implements MatcherInterface
{
    use RegexMatcherTrait;

    const REGEX = "{^[a-z0-9'][a-zA-Z0-9']*$}D";

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
        };
        return strlen($symbol)
            ? new MatchedToken(Token::terminatingSymbol($symbol), $streamWrapper->getPosition())
            : null;
    }
}
