<?php

namespace Polygen\Stream;

use Polygen\Language\Lexing\Lexer;
use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;

class TokenStream implements TokenStreamInterface
{
    /**
     * @var \Generator
     */
    private $tokenIterator;

    /**
     * @var Token
     */
    private $currentToken;

    /**
     * @var boolean
     */
    private $isEof = false;


    /**
     * Constructor.
     *
     * @param Lexer $stream
     */
    public function __construct(Lexer $lexer)
    {
        $this->tokenIterator = $lexer->getTokens();
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
        $this->currentToken = $this->readStream();
    }

    public function nextToken()
    {
        return $this->isEOF() ? null : $this->currentToken;
    }

    /**
     * @return Token|null
     */
    private function readStream()
    {
        while ($this->tokenIterator->valid()) {
            $token = $this->tokenIterator->current();
            /** @var Token $token */
            $this->tokenIterator->next();
            if (
                $token->getType() === Type::whitespace()
                || $token->getType() === Type::comment()
            ) {
                continue;
            }
            return $this->currentToken = $token;
        }
        $this->isEof = true;
        return null;
    }

    public function isEOF()
    {
        return $this->isEof;
    }
}
