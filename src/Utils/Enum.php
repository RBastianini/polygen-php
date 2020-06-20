<?php

namespace Polygen\Utils;

/**
 * Abstract class from which all enums should inherit from.
 */
abstract class Enum
{
    use Unserializable;

    /**
     * @var string
     */
    private $value;

    /**
     * Avoid having multiple "Type" objects around when we really need just one, by implementing a sort of flyweight
     * pattern.
     *
     * @var static[][]
     */
    private static $instances = [];

    /**
     * @var string[][]
     */
    private static $values;

    /**
     * Type constructor.
     *
     * @param string $value
     * @throws \InvalidArgumentException If the specified type is invalid.
     */
    private function __construct($value)
    {
        if (!static::ownsValue($value)) {
            throw new \InvalidArgumentException("Unknown enum value '$value' for class " . get_called_class() . '.');
        }
        $this->value = $value;
    }

    /**
     * Returns all the possible values for this enum.
     *
     * @return string[]
     * @throws \ReflectionException
     */
    private static function values()
    {
        if (!isset(self::$values[static::class])) {
            self::$values[static::class] = (new \ReflectionClass(static::class))->getConstants();
        }
        return self::$values[static::class];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Utility method to get an enum from a constant.
     *
     * @param string $type One of this class constants.
     * @return static
     */
    public static function fromValue($type)
    {
        if (array_key_exists(static::class, self::$instances) && array_key_exists($type, self::$instances[static::class])) {
            return self::$instances[static::class][$type];
        }
        return self::$instances[static::class][$type] = new static($type);
    }

    /**
     * Intercepts calls to all static method and attempts to convert them into a static factory method for the
     * specified enum type.
     *
     * @param string $method
     * @return static
     */
    public static function __callStatic($method, $_)
    {
        $tokenType = strtoupper(preg_replace('{(?<!^)[A-Z]}', '_$0', $method));
        return static::fromValue($tokenType);
    }

    /**
     * @param string $value
     * @return bool
     * @throws \ReflectionException
     */
    private static function ownsValue($value)
    {
        return array_key_exists($value, static::values());
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
