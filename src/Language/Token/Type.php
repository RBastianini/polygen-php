<?php

namespace Polygen\Language\Token;

final class Type
{
    const LEFT_BRACKET = 'LEFT_BRACKET';
    const RIGHT_BRACKET = 'RIGHT_BRACKET';
    const LEFT_SQUARE_BRACKET = 'LEFT_SQUARE_BRACKET';
    const RIGHT_SQUARE_BRACKET = 'RIGHT_SQUARE_BRACKET';
    const LEFT_CURLY_BRACKET = 'LEFT_CURLY_BRACKET';
    const RIGHT_CURLY_BRACKET = 'RIGHT_CURLY_BRACKET';
    const UNDERSCORE = 'UNDERSCORE';
    const NON_TERMINATING_SYMBOL = 'NON_TERMINATING_SYMBOL';
    const TERMINATING_SYMBOL = 'TERMINATING_SYMBOL';
    const COMMENT = 'COMMENT';
    const SEMICOLON = 'SEMICOLON';
    const DEFINITION = 'DEFINITION';
    const ASSIGNMENT = 'ASSIGNMENT';
    const PIPE = 'PIPE';
    const UNFOLDING = 'UNFOLDING';
    const FOLDING = 'FOLDING';
    const LEFT_DEEP_UNFOLDING = 'LEFT_DEEP_UNFOLDING';
    const RIGHT_DEEP_UNFOLDING = 'RIGHT_DEEP_UNFOLDING';
    const STAR = 'STAR';
    const PLUS = 'PLUS';
    const MINUS = 'MINUS';
    const CAP = 'CAP';
    const DOT = 'DOT';
    const COMMA = 'COMMA';
    const LEFT_DOT_BRACKET = 'LEFT_DOT_BRACKET';
    const RIGHT_DOT_BRACKET = 'RIGHT_DOT_BRACKET';
    const QUOTE = 'QUOTE';
    const BACKSLASH = 'BACKSLASH';
    const SLASH = 'SLASH';
    const DOT_LABEL = 'DOT_LABEL';
    const WHITESPACE = 'WHITESPACE';

    /**
     * @var
     */
    const TOKENS = [
        self::LEFT_BRACKET,
        self::RIGHT_BRACKET,
        self::LEFT_SQUARE_BRACKET,
        self::RIGHT_SQUARE_BRACKET,
        self::LEFT_CURLY_BRACKET,
        self::RIGHT_CURLY_BRACKET,
        self::UNDERSCORE,
        self::NON_TERMINATING_SYMBOL,
        self::TERMINATING_SYMBOL,
        self::COMMENT,
        self::SEMICOLON,
        self::DEFINITION,
        self::ASSIGNMENT,
        self::PIPE,
        self::UNFOLDING,
        self::FOLDING,
        self::LEFT_DEEP_UNFOLDING,
        self::RIGHT_DEEP_UNFOLDING,
        self::STAR,
        self::PLUS,
        self::MINUS,
        self::CAP,
        self::DOT,
        self::COMMA,
        self::LEFT_DOT_BRACKET,
        self::RIGHT_DOT_BRACKET,
        self::QUOTE,
        self::BACKSLASH,
        self::SLASH,
        self::DOT_LABEL,
        self::WHITESPACE
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * Avoid having multiple "Type" objects around when we really need just one, by implementing a sort of flyweight
     * pattern.
     *
     * @var array
     */
    private static $flyWeight = [];

    /**
     * Type constructor.
     *
     * @param string $kind
     * @throws \InvalidArgumentException If the specified type is invalid.
     */
    private function __construct($kind)
    {
        if (!in_array($kind, self::TOKENS)) {
            throw new \InvalidArgumentException("Unknown token type '$kind'.");
        }
        $this->type = $kind;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->type;
    }

    /**
     * Utility method to get a token type object of the specified kind.
     *
     * @param string $type One of this class constants.
     * @return static
     */
    public static function ofKind($type)
    {
        if (array_key_exists($type, self::$flyWeight)) {
            return self::$flyWeight[$type];
        }
        return self::$flyWeight[$type] = new static($type);
    }

    /**
     * Intercepts calls to all static method and attempts to convert them into a static factory method for the
     * specified token type.
     *
     * @param string $method
     * @return static
     */
    public static function __callStatic($method, $_)
    {
        $tokenType = strtoupper(preg_replace('{(?<!^)[A-Z]}', '_$0', $method));
        return self::ofKind($tokenType);
    }
}
