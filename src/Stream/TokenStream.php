<?php

namespace Polygen\Stream;

use Polygen\Language\Lexing\Lexer;
use Polygen\Language\Lexing\Matching\MatchedToken;
use Polygen\Language\Token\Type;

class TokenStream implements TokenStreamInterface
{
    /**
     * @var \Generator
     */
    private $matchingResultIterator;

    /**
     * @var MatchedToken
     */
    private $currentMatchingResult;

    /**
     * @var boolean
     */
    private $isEof = false;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * Constructor.
     *
     * @param Lexer $stream
     */
    public function __construct(Lexer $lexer)
    {
        $this->matchingResultIterator = $lexer->getTokens();
        $this->lexer = $lexer;
        $this->advance();
    }

    /**
     * @return void
     */
    public function advance()
    {
        if ($this->isEOF()) {
            throw new \RuntimeException("Trying to read past the end of file.");
        }
        $this->currentMatchingResult = $this->readStream();
    }

    public function nextToken()
    {
        return $this->isEOF() ? null : $this->currentMatchingResult;
    }

    /**
     * @return MatchedToken|null
     */
    private function readStream()
    {
        while ($this->matchingResultIterator->valid()) {
            $matchingResult = $this->matchingResultIterator->current();
            /** @var MatchedToken $matchingResult */
            $this->matchingResultIterator->next();
            if (
                $matchingResult->getToken()->getType() === Type::whitespace()
                || $matchingResult->getToken()->getType() === Type::comment()
            ) {
                continue;
            }
            return $this->currentMatchingResult = $matchingResult;
        }
        $this->isEof = true;
        return null;
    }

    public function isEOF()
    {
        return $this->isEof;
    }
}
