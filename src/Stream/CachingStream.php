<?php

namespace Polygen\Stream;

use Polygen\Language\Lexing\Matching\MatchedToken;
use Webmozart\Assert\Assert;

/**
 * A decorator for a TokenStreamInterface object that remembers everything it has read so far and allows rewinding the
 * stream to any past position.
 */
class CachingStream implements TokenStreamInterface
{
    /**
     * @var TokenStreamInterface
     */
    private $tokenStream;

    /**
     * @var MatchedToken[]
     */
    private $buffer = [];

    private $bufferPointer = 0;

    public function __construct(TokenStreamInterface $stream)
    {
        $this->tokenStream = $stream;
    }

    /**
     * @return void
     */
    public function advance()
    {
        if ($this->isEOF()) {
            throw new \RuntimeException("Trying to read past the end of the stream.");
        }
        $this->bufferPointer++;
        $this->fillTokenToBufferPointer();
    }

    /**
     * @return MatchedToken
     */
    public function nextToken()
    {
        $this->fillTokenToBufferPointer();
        return $this->buffer[$this->bufferPointer];
    }

    /**
     * @return bool
     */
    public function isEOF()
    {
        if ($this->buffer === [] && $this->tokenStream->isEOF()) {
            return true;
        }
        return $this->bufferPointer === $this->getBufferSize() - 1 && $this->tokenStream->isEOF();
    }

    /**
     * Repositions the stream at the specified offset. 0 is the start of the stream.
     * @return int
     */
    public function tell()
    {
        return $this->bufferPointer;
    }

    /**
     * @param int $offset
     * @return void
     */
    public function seek($offset)
    {
        Assert::greaterThanEq($offset, 0, "Offset can't be negative.");
        Assert::integer($offset, "Offset must be an integer.");
        Assert::greaterThanEq($this->bufferPointer, $offset, "Can't seek ahead of the cache.");
        $this->bufferPointer = $offset;
    }

    /**
     * If the buffer pointer is ahead of the buffer, fill the buffer.
     */
    private function fillTokenToBufferPointer()
    {
        while ($this->bufferPointer >= $bufferSize = $this->getBufferSize()) {
            $this->buffer[$bufferSize] = $this->tokenStream->nextToken();
            $this->tokenStream->advance();
        }
    }

    private function getBufferSize()
    {
        return count($this->buffer);
    }
}
