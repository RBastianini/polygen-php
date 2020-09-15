<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;

/**
 * Matches spaces, tabs, carriage returns and "empty strings".
 */
class WhitespaceMatcher implements MatcherInterface
{
    /**
     * @return MatchedToken|null
     */
    public function match(MatcherInput $streamWrapper)
    {
        $hasMatched = false;
        while (($char = $streamWrapper->peek()) !== null && $this->isBlank($char)) {
            $hasMatched = true;
            $streamWrapper->read();
        }
        return $hasMatched
            ? new MatchedToken(Token::whitespace(), $streamWrapper->getPosition())
            : null;
    }

    /**
     * Returns true if the passed character is considered a whitespace char (space, tab, return).
     *
     * @param string $char
     * @return bool
     */
    private function isBlank($char)
    {
        return $char !== '' && strpos("\t\r\n ", $char) !== false;
    }
}
