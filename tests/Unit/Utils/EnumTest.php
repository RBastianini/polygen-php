<?php

namespace Tests\Polygen\Unit\Utils
{

    use Tests\Polygen\Unit\Utils\EnumTest\DummyEnum;
    use Tests\TestCase;

    class EnumTest extends TestCase
    {
        /**
         * @test
         */
        public function it_throws_exception_if_constructed_with_an_unsupported_value()
        {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage("Unknown enum value 'other' for class " . DummyEnum::class . '.');

            DummyEnum::fromValue('other');
        }
    }
}

namespace Tests\Polygen\Unit\Utils\EnumTest
{

    use Polygen\Utils\Enum;

    class DummyEnum extends Enum
    {
        const THIS = 'this';
        const THAT = 'that';
    }
}
