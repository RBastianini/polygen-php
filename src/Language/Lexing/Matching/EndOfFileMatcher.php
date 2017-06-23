<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;

/**
 * Matches the empty string that is read when the file ends.
 */
class EndOfFileMatcher extends BaseMatcher
{
    /**
     * Actually does the matching.
     *
     * @return Token|null
     */
    protected function doMatch()
    {
        if ($this->peek() === '') {
            $this->read();
            return Token::endOfFile();
        }
        return null;
    }
}
