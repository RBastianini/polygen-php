<?php

namespace Tests\Unit\Language\Token;

use Polygen\Language\Token\Type;
use Tests\TestCase;

class TypeTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_with_a_valid_type_using_for_type()
    {
        $SUT = Type::ofKind(Type::DEFINITION);
        $this->assertInstanceOf(Type::class, $SUT);
    }

    /**
     * @test
     */
    public function it_blows_up_if_constructed_with_an_invalid_type()
    {
        $unknownType = "Barbapapà";
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown token type '$unknownType'.");
        Type::ofKind($unknownType);
    }

    /**
     * @test
     */
    public function it_does_not_return_multiple_different_objects_of_the_same_kind()
    {
        $this->assertSame(Type::ofKind(Type::DEFINITION), Type::ofKind(Type::DEFINITION));
    }

    /**
     * @test
     */
    public function it_constructs_objects_via_magic_static_call()
    {
        $this->assertSame(Type::ofKind(Type::LEFT_SQUARE_BRACKET), Type::leftSquareBracket());
    }
}
