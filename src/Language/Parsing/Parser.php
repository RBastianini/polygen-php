<?php

namespace Polygen\Language\Parsing;

use Polygen\Language\Exceptions\Parsing\UnexpectedTokenException;
use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;
use Polygen\Language\Lexing\Matching\MatchedToken;
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
     * @return null|MatchedToken
     */
    protected function peek()
    {
        return $this->stream->nextToken();
    }

    /**
     * @param Type ...$type
     * @return Token
     * @throws UnexpectedTokenException
     */
    protected function readToken(Type ...$type)
    {
        $matchedToken = $this->doReadTokenIfType($type);
        if ($matchedToken === null) {
            throw new UnexpectedTokenException($type, $this->peek()->getToken(), $this->peek()->getPosition());
        }
        return $matchedToken->getToken();
    }

    /**
     * @param Type[] ...$type
     * @return null|Token
     */
    protected function readTokenIfType(Type ...$types)
    {
        $matchedToken = $this->doReadTokenIfType($types);
        return $matchedToken
            ? $matchedToken->getToken()
            : null;
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
        return in_array($this->peek()->getToken()->getType(), $types);
    }

    /**
     * @return bool
     */
    protected function isEndOfDocument()
    {
        return $this->stream->isEOF();
    }

    /**
     * @param array $types
     * @return MatchedToken|null
     */
    private function doReadTokenIfType(array $types)
    {
        $matchedToken = $this->isNextTokenOfType(...$types)
            ? $this->peek()
            : null;
        if ($matchedToken) {
            $this->stream->advance();
        }
        return $matchedToken;
    }
}
