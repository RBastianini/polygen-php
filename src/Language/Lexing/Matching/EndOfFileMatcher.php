<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;

/**
 * Matches the empty string that is read when the file ends.
 */
class EndOfFileMatcher implements MatcherInterface
{
    /**
     * @return MatchedToken|null
     */
    public function match(MatcherInput $streamWrapper)
    {
        if ($streamWrapper->peek() === '') {
            $streamWrapper->read();
            return new MatchedToken(Token::endOfFile(), $streamWrapper->getPosition());
        }
        return null;
    }
}
