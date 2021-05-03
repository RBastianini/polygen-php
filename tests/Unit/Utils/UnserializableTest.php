<?php

namespace Tests\Polygen\Unit\Utils {

    use Tests\Polygen\Unit\Utils\UnserializableTest\UnserializableDummy;
    use Tests\TestCase;

    class UnserializableTest extends TestCase
    {
        /**
         * @test
         */
        public function it_cannot_be_serialized()
        {
            $sut = new UnserializableDummy();

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage(UnserializableDummy::class . " can't be serialized directly.");

            serialize($sut);

        }

        /**
         * @test
         */
        public function it_cannot_be_unserialized()
        {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage(UnserializableDummy::class . " can't be unserialized directly.");

            unserialize('O:63:"Tests\Polygen\Unit\Utils\UnserializableTest\UnserializableDummy":0:{}');
        }
    }
}

namespace Tests\Polygen\Unit\Utils\UnserializableTest
{
    use Polygen\Utils\Unserializable;

    class UnserializableDummy
    {
        use Unserializable;
    }
}
