<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;

/**
 * Matcher for one-char long symbols.
 */
class ShortSymbolMatcher extends BaseMatcher
{
    const MATCHING_RULES = [
        Type::LEFT_BRACKET => '(',
        Type::RIGHT_BRACKET => ')',
        Type::LEFT_SQUARE_BRACKET => '[',
        Type::RIGHT_SQUARE_BRACKET => ']',
        Type::LEFT_CURLY_BRACKET => '{',
        Type::RIGHT_CURLY_BRACKET => '}',
        Type::UNDERSCORE => '_',
        Type::SEMICOLON => ';',
        Type::PIPE => '|',
        Type::UNFOLDING => '>',
        Type::FOLDING => '<',
        Type::STAR => '*',
        Type::PLUS => '+',
        Type::MINUS => '-',
        Type::CAP => '^',
        Type::DOT => '.',
        Type::COMMA => ',',
        Type::SLASH => '/',
        Type::COLON => ':',
        Type::BACKSLASH => '\\',
    ];

    /**
     * {@inheritdoc}
     */
    protected function doMatch()
    {
        $char = $this->read();
        if ($char === null) {
            return null;
        }
        foreach (self::MATCHING_RULES as $tokenName => $match) {
            if ($char === $match) {
                return Token::ofType($tokenName);
            }
        }
    }
}
