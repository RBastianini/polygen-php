<?php

namespace Polygen\Stream;

use Polygen\Language\Lexing\Matching\MatchedToken;
use Webmozart\Assert\Assert;

/**
 * A stream able to create savepoints to later restart from.
 * Useful for situations in which parsing continued with a wrong assumption and needs to backtrack in order to reparse
 * some tokens.
 * Builds on the CachingStream in order to keep cache handling separate.
 */
class SavePointStream implements TokenStreamInterface
{
    /**
     * @var CachingStream
     */
    private $stream;

    /**
     * @var int[]
     */
    private $savePoints = [];

    public function __construct(CachingStream $stream)
    {
        $this->stream = $stream;
    }

    /**
     * @return void
     */
    public function advance()
    {
        $this->stream->advance();
    }

    /**
     * @return MatchedToken
     */
    public function nextToken()
    {
        return $this->stream->nextToken();
    }

    /**
     * @return bool
     */
    public function isEOF()
    {
        return $this->stream->isEOF();
    }

    /**
     * @return void
     */
    public function createSavePoint()
    {
        $this->savePoints[] = $this->stream->tell();
    }

    /**
     * @return void
     */
    public function rollback()
    {
        Assert::notEmpty($this->savePoints, 'Cannot rollback stream: no savepoints defined.');
        $this->stream->seek(array_pop($this->savePoints));
    }
}
