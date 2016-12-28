<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;

/**
 * Matches spaces, tabs, carriage returns and "empty strings".
 */
class WhitespaceMatcher extends BaseMatcher
{
    /**
     * {@inheritdoc}
     */
    public function doMatch()
    {
        $hasMatched = false;
        while (($char = $this->peek()) !== null && $this->isBlank($char)) {
            $hasMatched = true;
            $this->read();
        }
        return $hasMatched ? Token::whitespace() : null;
    }

    /**
     * Returns true if the passed character is considered a whitespace char (space, tab, return).
     *
     * @param string $char
     * @return bool
     */
    private function isBlank($char)
    {
        return $char === '' || strpos("\t\r\n ", $char) !== false;
    }
}
