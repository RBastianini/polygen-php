<?php

namespace Polygen\Language\Parsing;

use Polygen\Language\Exceptions\Parsing\UnexpectedTokenException;
use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;
use Polygen\Stream\SavePointStream;

/**
 * Base class that hides out the stream to the actual parser and exposes a few utility method to make the syntax a bit
 * less verbose.
 */
abstract class Parser
{
    /**
     * @var SavePointStream
     */
    private $stream;

    /**
     * Parser constructor.
     */
    public function __construct(SavePointStream $stream)
    {
        $this->stream = $stream;
    }

    /**
     * @return null|Token
     */
    protected function peek()
    {
        return $this->stream->nextToken();
    }

    /**
     * @param Type ...$type
     * @return Token
     * @throw UnexpectedTokenException
     */
    protected function readToken(Type ...$type)
    {
        $token = $this->readTokenIfType(...$type);
        if ($token === null) {
            throw new UnexpectedTokenException($type, $this->peek());
        }
        return $token;
    }

    /**
     * @param Type[] ...$type
     * @return null|Token
     */
    protected function readTokenIfType(Type ...$types)
    {
        $toReturn = $this->isNextTokenOfType(...$types) ? $this->peek() : null;
        if ($toReturn) {
            $this->stream->advance();
        }
        return $toReturn;
    }

    protected function createSavePoint()
    {
        $this->stream->createSavePoint();
    }

    protected function rollback()
    {
        $this->stream->rollback();
    }

    /**
     * @param Type[] ...$types
     * @return bool
     */
    protected function isNextTokenOfType(Type ...$types)
    {
        return in_array($this->peek()->getType(), $types);
    }

    /**
     * @return bool
     */
    protected function isEndOfDocument()
    {
        return $this->stream->isEOF();
    }
}
