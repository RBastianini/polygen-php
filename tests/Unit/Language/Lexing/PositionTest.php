<?php

namespace Tests\Polygen\Unit\Language\Lexing;

use Polygen\Language\Lexing\Position;
use Tests\TestCase;

class PositionTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_the_line()
    {
        $sut = new Position($line = 123, 456);

        $this->assertEquals($line, $sut->getLine());
    }

    /**
     * @test
     */
    public function it_returns_the_column()
    {
        $sut = new Position(123, $column = 456);

        $this->assertEquals($column, $sut->getColumn());
    }
}
