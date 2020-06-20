<?php

namespace Polygen\Language\Token;

use Polygen\Utils\Unserializable;

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
 * @method static Token endOfFile()
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
    use Unserializable;

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
     * Rebuilds a Token from a serializable array.
     * @param array $serializableArray The result of a call to $token->toSerializableArray()
     * @see Token::toSerializableArray()
     * @return static
     */
    public static function fromSerializableArray(array $serializableArray)
    {
        return Token::ofType(... $serializableArray);
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
        return sprintf(
            "<%s>",
            implode(', ', array_filter([$this->getType(), $this->getValue()]))
        );
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
    {return $this->value;
    }

    /**
     * Returns an array that can be used to unserialize a token in a way that preserve it being a singleton.
     * @see Token::fromSerializableArray()
     * @return array
     */
    public function toSerializableArray()
    {
        return [(string) $this->getType(), $this->getValue()];
    }
}
