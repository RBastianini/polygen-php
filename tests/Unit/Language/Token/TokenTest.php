<?php

namespace Tests\Unit\Language\Token;

use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_with_a_factory_method()
    {
        $value = 'blah';
        $SUT = Token::ofType(Type::COMMENT, $value);
        $this->assertInstanceOf(Token::class, $SUT);
        $this->assertEquals(Type::COMMENT, $SUT->getType());
        $this->assertEquals($value, $SUT->getValue());

    }

    /**
     * @test
     */
    public function it_can_be_constructed_with_magic_static_factory_methods()
    {
        $value = 'blah';
        $this->assertEquals(Token::ofType(Type::COMMENT, $value), Token::comment($value));
    }

    /**
     * @test
     */
    public function it_can_be_converted_to_string()
    {
        $type = Type::COMMENT;
        $value = 'blah';
        $this->assertEquals("<$type, $value>", Token::comment($value));
    }
}
