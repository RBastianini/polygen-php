<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;

/**
 * Matches multi-character symbols.
 */
class LongSymbolMatcher extends BaseMatcher
{
    const MATCHING_RULES = [
        Type::ASSIGNMENT => '{:=}',
        Type::LEFT_DEEP_UNFOLDING => '{>>}',
        Type::RIGHT_DEEP_UNFOLDING => '{<<}',
        Type::LEFT_DOT_BRACKET => '{\.\(}',
        Type::RIGHT_DOT_BRACKET => '{\)\.}',
    ];

    /**
     * {@inheritdoc}
     */
    protected function doMatch()
    {
        $string = $this->read(2);
        if (strlen($string) < 2) {
            return null;
        }
        foreach (self::MATCHING_RULES as $tokenName => $regex) {
            if (preg_match($regex, $string) === 1) {
                return Token::ofType($tokenName);
            }
        }
    }
}
