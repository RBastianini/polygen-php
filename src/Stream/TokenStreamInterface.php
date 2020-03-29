<?php

namespace Polygen\Stream;

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
     * @return \Polygen\Language\Token\Token
     */
    public function nextToken();

    /**
     * Checks whether the stream is at its end or not.
     *
     * @return bool
     */
    public function isEOF();
}
