<?php

namespace Polygen\Stream;

use Polygen\Language\Lexing\Matching\MatchedToken;

interface TokenStreamInterface
{
    /**
     * Moves the stream to the next token.
     *
     * @return void
     */
    public function advance();

    /**
     * Reads the token at the current stream position.
     *
     * @return MatchedToken
     */
    public function nextToken();

    /**
     * Checks whether the stream is at its end or not.
     *
     * @return bool
     */
    public function isEOF();
}
