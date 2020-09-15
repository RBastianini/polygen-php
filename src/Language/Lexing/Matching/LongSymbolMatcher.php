<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;

/**
 * Matches multi-character symbols.
 */
class LongSymbolMatcher implements MatcherInterface
{
    const MATCHING_RULES = [
        Type::ASSIGNMENT => ':=',
        Type::LEFT_DEEP_UNFOLDING => '>>',
        Type::RIGHT_DEEP_UNFOLDING => '<<',
        Type::LEFT_DOT_BRACKET => '.(',
    ];

    /**
     * @return MatchedToken|null
     */
    public function match(MatcherInput $streamWrapper)
    {
        $string = $streamWrapper->read(2);
        if (strlen($string) < 2) {
            return null;
        }
        foreach (self::MATCHING_RULES as $tokenName => $regex) {
            if ($regex === $string) {
                return new MatchedToken(Token::ofType($tokenName), $streamWrapper->getPosition());
            }
        }
    }
}
