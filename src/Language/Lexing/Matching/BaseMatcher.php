<?php

namespace Polygen\Language\Lexing\Matching;

use GuzzleHttp\Stream\StreamInterface;

/**
 * Base class for matchers.
 * Matchers will attempt to parse as much of the string as they can and return a token out of the parsed text.
 */
abstract class BaseMatcher
{
    /**
     * @var StreamInterface
     */
    private $stream;

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
     * {@inheritdoc}
     */
    public function next()
    {
        // Attempts to match the next token, resets the stream to the previous state if matching fails.
        $streamPosition = $this->tell();
        $token = $this->doMatch();
        if (!$token) {
            $this->stream->seek($streamPosition);
        }
        return $token;
    }

    /**
     * Actually does the matching.
     *
     * @return mixed
     */
    protected abstract function doMatch();

    /**
     * Utility method to look at the next characters in the stream without consuming them.
     *
     * @param int $chars
     * @return null|string
     */
    protected function peek($chars = 1)
    {
        if ($this->stream->eof()) {
            return null;
        }
        $streamPosition = $this->tell();
        $token = $this->read($chars);
        $this->stream->seek($streamPosition);
        return $token;
    }

    /**
     * Utility method to read the next characters in the stream.
     *
     * @param int $chars
     * @return null|string
     */
    protected function read($chars = 1)
    {
        return $this->stream->eof() ? null : $this->stream->read($chars);
    }

    /**
     * Utility method to get the position in the stream.
     *
     * @return bool|int
     */
    protected function tell()
    {
        return $this->stream->tell();
    }

    /**
     * Utility method to move around in the stream.
     *
     * @param int $position
     * @return bool
     */
    protected function seek($position)
    {
        return $this->stream->seek($position, SEEK_CUR);
    }

    /**
     * Utility method to check if a string matches a regex stored in on of the class' constants.
     *
     * @param string $string
     * @param string $regexName
     * @return bool
     */
    protected function matchesRegex($string, $regexName = 'REGEX')
    {
        return preg_match(constant(static::class . "::$regexName"), $string) === 1;
    }
}
