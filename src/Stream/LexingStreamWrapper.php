<?php

namespace Polygen\Stream;

use GuzzleHttp\Stream\StreamInterface;
use Polygen\Language\Lexing\Matching\MatcherInterface;
use Polygen\Language\Lexing\Matching\MatcherInput;
use Polygen\Language\Lexing\Matching\MatchedToken;
use Polygen\Language\Lexing\Matching\TokenMatcher;
use Polygen\Language\Lexing\Position;

/**
 * Wraps a stream providing tentative matcher interface.
 */
class LexingStreamWrapper implements MatcherInput, TokenMatcher
{
    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * @var int
     */
    private $currentLine = 1;

    /**
     * @var int
     */
    private $currentColumn = 1;

    /**
     * Constructor.
     *
     * @param StreamInterface $stream
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Tries matching a token with the provided matcher or resets the stream if matching is not possible.
     *
     * @return MatchedToken|null
     */
    public function tryMatchWith(MatcherInterface $matcher)
    {
        // Attempts to match the next token, resets the stream to the previous state if matching fails.
        $streamPosition = $this->tell();
        $previousLine = $this->currentLine;
        $previousColumn = $this->currentColumn;
        $match = $matcher->match($this);
        if (!$match) {
            $this->stream->seek($streamPosition);
            $this->currentLine = $previousLine;
            $this->currentColumn = $previousColumn;
        }
        return $match;
    }

    /**
     * Utility method to look at the next characters in the stream without consuming them.
     *
     * @param int $chars
     * @return string|null
     */
    public function peek($chars = 1)
    {
        if ($this->stream->eof()) {
            return null;
        }
        $streamPosition = $this->stream->tell();
        $string = $this->doRead($chars);
        $this->stream->seek($streamPosition);
        return $string;
    }

    /**
     * Utility method to read the next characters in the stream.
     *
     * @param int $chars
     * @return null|string
     */
    public function read($chars = 1)
    {
        $read = $this->doRead($chars);
        if ($read === null) {
            return null;
        }
        $carriageReturns = substr_count($read, PHP_EOL);
        // Reset the column counter when a carriage return was found
        if ($carriageReturns) {
            $this->currentLine += $carriageReturns;
            // Calculate how many characters there are in the read string, after the last carriage return found.
            // If the carriage return was on the last column, set the column to one.
            $this->currentColumn = max(
                strlen(substr($read, (int) strrpos($read, PHP_EOL) + 1)),
                1
            );
        } else {
            $this->currentColumn += strlen($read);
        }
        return $read;
    }

    /**
     * Internal method that reads and returns at most $chars characters from the stream.
     *
     * @param int $chars
     * @return string|null
     */
    private function doRead($chars)
    {
        if ($this->stream->eof()) {
            return null;
        }
        return $this->stream->read($chars);
    }

    /**
     * Utility method to get the position in the stream.
     *
     * @return bool|int
     */
    private function tell()
    {
        return $this->stream->tell();
    }

    /**
     * @return bool
     */
    public function isDoneMatching()
    {
        return $this->stream->eof();
    }

    public function getPosition()
    {
        return new Position($this->currentLine, $this->currentColumn);
    }
}
