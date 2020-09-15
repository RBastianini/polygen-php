<?php

namespace Polygen\Language\Lexing\Matching;

/**
 * Interface for matchers.
 */
interface MatcherInterface
{
    /**
     * Given a LexingStreamWrapper, this method is expected to consume it and return a token, or null if matching is
     * not possible.
     *
     * @return MatchedToken|null
     */
    public function match(MatcherInput $lexingStreamWrapper);
}
