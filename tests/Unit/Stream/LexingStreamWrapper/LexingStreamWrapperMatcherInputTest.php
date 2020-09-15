<?php

namespace Tests\Polygen\Unit\Stream\LexingStreamWrapper;

use GuzzleHttp\Stream\StreamInterface;
use Mockery;
use Mockery\MockInterface;
use Polygen\Language\Lexing\Matching\MatcherInput;
use Polygen\Language\Lexing\Position;
use Polygen\Stream\LexingStreamWrapper;
use Tests\StreamUtils;
use Tests\TestCase;

/**
 * Tests the MatcherInput interface methods of the LexingStreamWrapper
 */
class LexingStreamWrapperMatcherInputTest extends TestCase
{
    use StreamUtils;

    /**
     * @var StreamInterface|MockInterface
     */
    private $stream;

    /**
     * @var MatcherInput
     */
    private $sut;

    protected function setUp()
    {
        parent::setUp();
        $this->stream = Mockery::mock(StreamInterface::class);
        $this->sut = new LexingStreamWrapper($this->stream);
    }

    /**
     * @test
     */
    public function it_can_peek_ahead()
    {
        $numChars = 3;

        $this->stream->shouldReceive('eof')
            ->once()
            ->ordered()
            ->andReturnFalse();

        $this->stream->shouldReceive('tell')
            ->once()
            ->ordered()
            ->andReturn($streamPosition = 10);

        // Called by "read" internal call
        $this->stream->shouldReceive('eof')
            ->once()
            ->ordered()
            ->andReturnFalse();

        $this->stream->shouldReceive('read')
            ->with($numChars)
            ->once()
            ->ordered()
            ->andReturn($peekedText = 'abc');

        $this->stream->shouldReceive('seek')
            ->with($streamPosition)
            ->once()
            ->ordered();

        $result = $this->sut->peek($numChars);

        $this->assertEquals($peekedText, $result);
    }

    /**
     * @test
     */
    public function it_returns_null_when_peeking_at_the_end_of_the_stream()
    {
        $this->stream->shouldReceive('eof')
            ->once()
            ->andReturnTrue();

        $this->stream->shouldNotReceive('read');

        $result = $this->sut->peek(3);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_can_read_ahead()
    {
        $numChars = 3;

        $this->stream->shouldReceive('eof')
            ->once()
            ->ordered()
            ->andReturnFalse();

        $this->stream->shouldReceive('read')
            ->with($numChars)
            ->once()
            ->ordered()
            ->andReturn($peekedText = 'abc');

        $result = $this->sut->read($numChars);

        $this->assertEquals($result, $peekedText);
    }

    /**
     * @test
     */
    public function it_returns_null_when_reading_at_the_end_of_the_stream()
    {
        $this->stream->shouldReceive('eof')
            ->once()
            ->ordered()
            ->andReturnTrue();

        $result = $this->sut->read(3);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_returns_the_stream_position()
    {
        $readChars = ['a', 'b', 'c', "\n", "\n", 'd', 'e', 'f', "\n", 'g', 'h', "\n"];
        $this->stream->shouldReceive('eof')
            ->andReturn(... array_merge(array_fill(0, count($readChars), false), [true]));

        $this->stream->shouldReceive('read')
            ->with(Mockery::any())
            ->andReturn(... $readChars);

        $this->assertEquals(new Position(1, 1), $this->sut->getPosition());
        $this->sut->read();
        $this->assertEquals(new Position(1, 2), $this->sut->getPosition());
        $this->sut->read();
        $this->assertEquals(new Position(1, 3), $this->sut->getPosition());
        $this->sut->read();
        $this->assertEquals(new Position(1, 4), $this->sut->getPosition());
        $this->sut->read();
        $this->assertEquals(new Position(2, 1), $this->sut->getPosition());
        $this->sut->read();
        $this->assertEquals(new Position(3, 1), $this->sut->getPosition());
        $this->sut->read();
        $this->assertEquals(new Position(3, 2), $this->sut->getPosition());
        $this->sut->read();
        $this->assertEquals(new Position(3, 3), $this->sut->getPosition());
        $this->sut->read();
        $this->assertEquals(new Position(3, 4), $this->sut->getPosition());
        $this->sut->read();
        $this->assertEquals(new Position(4, 1), $this->sut->getPosition());
        $this->sut->read();
        $this->assertEquals(new Position(4, 2), $this->sut->getPosition());
    }
}
