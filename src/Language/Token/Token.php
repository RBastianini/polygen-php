<?php

namespace Polygen\Language\Token;

/**
 * Class Token
 *
 * @method static Token assignment()
 * @method static Token cap()
 * @method static Token comma()
 * @method static Token comment(string $comment)
 * @method static Token definition()
 * @method static Token dot()
 * @method static Token dotLabel(string $name)
 * @method static Token folding()
 * @method static Token leftDeepUnfolding()
 * @method static Token leftBracket()
 * @method static Token leftCurlyBracket()
 * @method static Token leftDotBracket()
 * @method static Token leftSquareBracket()
 * @method static Token minus()
 * @method static Token nonTerminatingSymbol(string $symbolName)
 * @method static Token plus()
 * @method static Token rightBracket()
 * @method static Token rightCurlyBracket()
 * @method static Token rightDeepUnfolding()
 * @method static Token rightDotBracket()
 * @method static Token rightSquareBracket()
 * @method static Token semicolon()
 * @method static Token slash()
 * @method static Token star()
 * @method static Token pipe()
 * @method static Token terminatingSymbol(string $stringContent)
 * @method static Token underscore()
 * @method static Token unfolding()
 * @method static Token whitespace()
 */
final class Token
{
	/**
	 * @var Type
	 */
	private $type;

	/**
	 * @var string|null
	 */
	private $value;

    /**
     * Token constructor.
     *
     * @param Type $type
     * @param null $value
     */
	private function __construct(Type $type, $value = null)
	{
        $this->type = $type;
        $this->value = $value;
	}

    /**
     * Intercepts calls to all static method and attempts to convert them into a static factory method for the
     * specified token type.
     *
     * @param string $method
     * @return static
     */
    public static function __callStatic($method, array $params = [])
    {
        return new static(Type::$method(), reset($params));
    }

    /**
     * Returns a token of the specified type, with the specified value.
     *
     * @param string $typeName
     * @param string|null $value
     * @return static
     */
    public static function ofType($typeName, $value = null)
    {
        return new static(Type::ofKind($typeName), $value);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "<$this->type, $this->value>";
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return null|string
     */
    public function getValue()
    {
        return $this->value;
    }
}
