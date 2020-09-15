<?php

namespace Polygen\Language\Lexing\Matching;

/**
 * Interface for objects able to match tokens using matchers.
 */
interface TokenMatcher
{
    /**
     * Tries matching a token with the provided matcher or resets the stream if matching is not possible.
     *
     * @return MatchedToken|null
     */
    public function tryMatchWith(MatcherInterface $matcher);

    /**
     * Returns true when there is nothing more to match.
     *
     * @return bool
     */
    public function isDoneMatching();

}
