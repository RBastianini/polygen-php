<?php

namespace Tests\Unit\Language\Lexing\Matching;

use GuzzleHttp\Stream\StreamInterface;
use Mockery;
use Mockery\MockInterface;
use Polygen\Language\Lexing\Matching\BaseMatcher;

class BaseMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StreamInterface|MockInterface
     */
    private $streamMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->streamMock = Mockery::mock(StreamInterface::class);
    }

    /**
     * @test
     */
    public function it_does_not_reset_the_stream_if_do_match_returns_a_value()
    {
        $matchedToken = 'something';
        /** @var BaseMatcher|Mockery\MockInterface $SUT */
        $SUT = Mockery::mock(BaseMatcher::class, [$this->streamMock])->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $SUT->shouldReceive('doMatch')
            ->once()
            ->andReturn($matchedToken);
        $this->streamMock->shouldReceive('tell')
            ->once()
            ->andReturn(30);
        $this->streamMock->shouldNotReceive('seek');
        $result = $SUT->next();
        $this->assertEquals($matchedToken, $result);
    }

    /**
     * @test
     */
    public function it_resets_the_stream_if_do_match_returns_null()
    {
        $previousPosition = 30;
        /** @var BaseMatcher|Mockery\MockInterface $SUT */
        $SUT = Mockery::mock(BaseMatcher::class, [$this->streamMock])->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $SUT->shouldReceive('doMatch')
            ->once()
            ->andReturn(null);
        $this->streamMock->shouldReceive('tell')
            ->once()
            ->andReturn($previousPosition);
        $this->streamMock->shouldReceive('seek')
            ->once()
            ->with($previousPosition);
        $result = $SUT->next();
        $this->assertNull($result);
    }
}
