<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Lexing\Position;

/**
 * Interface for methods to allow matchers to consume streams.
 */
interface MatcherInput
{
    /**
     * Utility method to look at the next characters in the stream without consuming them.
     *
     * @param int $chars
     * @return null|string
     */
    public function peek($chars = 1);

    /**
     * Utility method to read the next characters in the stream.
     *
     * @param int $chars
     * @return null|string
     */
    public function read($chars = 1);

    /**
     * @return Position
     */
    public function getPosition();
}
