<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Lexing\Position;
use Polygen\Language\Token\Token;

/**
 * Represents a result returned by a matcher. It contains the matched token and the position where it was matched.
 */
class MatchedToken
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var Position
     */
    private $tokenPosition;

    public function __construct(Token $token, Position $tokenPosition)
    {
        $this->token = $token;
        $this->tokenPosition = $tokenPosition;
    }

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return Position
     */
    public function getPosition()
    {
        return $this->tokenPosition;
    }
}
