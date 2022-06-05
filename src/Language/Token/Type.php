<?php

namespace Polygen\Language\Token;

use Polygen\Utils\Unserializable;

/**
 * @method static Type assignment()
 * @method static Type backslash()
 * @method static Type cap()
 * @method static Type colon()
 * @method static Type comma()
 * @method static Type comment()
 * @method static Type definition()
 * @method static Type dot()
 * @method static Type dotLabel()
 * @method static Type endOfFile()
 * @method static Type folding()
 * @method static Type leftBracket()
 * @method static Type leftCurlyBracket()
 * @method static Type leftDeepUnfolding()
 * @method static Type leftDotBracket()
 * @method static Type leftSquareBracket()
 * @method static Type minus()
 * @method static Type nonTerminatingSymbol()
 * @method static Type pipe()
 * @method static Type plus()
 * @method static Type quote()
 * @method static Type rightBracket()
 * @method static Type rightCurlyBracket()
 * @method static Type rightDeepUnfolding()
 * @method static Type rightSquareBracket()
 * @method static Type semicolon()
 * @method static Type star()
 * @method static Type terminatingSymbol()
 * @method static Type underscore()
 * @method static Type unfolding()
 * @method static Type whitespace()
  */
final class Type
{
    use Unserializable;

    const ASSIGNMENT = 'ASSIGNMENT';
    const BACKSLASH = 'BACKSLASH';
    const CAP = 'CAP';
    const COLON = 'COLON';
    const COMMA = 'COMMA';
    const COMMENT = 'COMMENT';
    const DEFINITION = 'DEFINITION';
    const DOT = 'DOT';
    const DOT_LABEL = 'DOT_LABEL';
    const END_OF_FILE = 'END_OF_FILE';
    const FOLDING = 'FOLDING';
    const LEFT_BRACKET = 'LEFT_BRACKET';
    const LEFT_CURLY_BRACKET = 'LEFT_CURLY_BRACKET';
    const LEFT_DEEP_UNFOLDING = 'LEFT_DEEP_UNFOLDING';
    const LEFT_DOT_BRACKET = 'LEFT_DOT_BRACKET';
    const LEFT_SQUARE_BRACKET = 'LEFT_SQUARE_BRACKET';
    const MINUS = 'MINUS';
    const NON_TERMINATING_SYMBOL = 'NON_TERMINATING_SYMBOL';
    const PIPE = 'PIPE';
    const PLUS = 'PLUS';
    const QUOTE = 'QUOTE';
    const RIGHT_BRACKET = 'RIGHT_BRACKET';
    const RIGHT_CURLY_BRACKET = 'RIGHT_CURLY_BRACKET';
    const RIGHT_DEEP_UNFOLDING = 'RIGHT_DEEP_UNFOLDING';
    const RIGHT_SQUARE_BRACKET = 'RIGHT_SQUARE_BRACKET';
    const SEMICOLON = 'SEMICOLON';
    const SLASH = 'SLASH';
    const STAR = 'STAR';
    const TERMINATING_SYMBOL = 'TERMINATING_SYMBOL';
    const UNDERSCORE = 'UNDERSCORE';
    const UNFOLDING = 'UNFOLDING';
    const WHITESPACE = 'WHITESPACE';

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

    private static $SUPPORTED_TOKENS = [];

    private static function GET_SUPPORTED_TOKENS() {
        if (empty(self::$SUPPORTED_TOKENS)) {
            self::$SUPPORTED_TOKENS = (new \ReflectionClass(__CLASS__))->getConstants();
        }
        return array_values(self::$SUPPORTED_TOKENS);
    }

    /**
     * Type constructor.
     *
     * @param string $kind
     * @throws \InvalidArgumentException If the specified type is invalid.
     */
    private function __construct($kind)
    {
        if (!in_array($kind, self::GET_SUPPORTED_TOKENS())) {
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
