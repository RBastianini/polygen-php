<?php

namespace Tests\Polygen\Unit\Stream\LexingStreamWrapper;

use GuzzleHttp\Stream\StreamInterface;
use Mockery;
use Mockery\MockInterface;
use Polygen\Language\Lexing\Matching\MatchedToken;
use Polygen\Language\Lexing\Matching\MatcherInterface;
use Polygen\Language\Lexing\Matching\TokenMatcher;
use Polygen\Language\Lexing\Position;
use Polygen\Stream\LexingStreamWrapper;
use Tests\StreamUtils;
use Tests\TestCase;

/**
 * Tests the TokenMatcher interface methods of the LexingStreamWrapper
 */
class LexingStreamWrapperTokenMatcherTest extends TestCase
{
    use StreamUtils;

    /**
     * @var StreamInterface|MockInterface
     */
    private $stream;

    /**
     * @var TokenMatcher
     */
    private $sut;
    /**
     * @var MatcherInterface|MockInterface
     */
    private $matcherInterface;

    protected function setUp()
    {
        parent::setUp();
        $this->stream = Mockery::mock(StreamInterface::class);
        $this->matcherInterface = Mockery::mock(MatcherInterface::class);
        $this->sut = new LexingStreamWrapper($this->stream);
    }

    /**
     * @test
     */
    public function it_returns_a_matched_token_if_the_matcher_returns_one()
    {
        $matchedToken = Mockery::mock(MatchedToken::class);

        $this->stream->shouldReceive('tell')
            ->once()
            ->andReturn($initialPosition = 0);

        $this->matcherInterface->shouldReceive('match')
            ->with($this->sut)
            ->once()
            ->andReturn($matchedToken);

        $result = $this->sut->tryMatchWith($this->matcherInterface);

        $this->assertSame($matchedToken, $result);
    }

    /**
     * @test
     */
    public function it_resets_the_stream_position_if_the_matcher_does_not_return_a_result()
    {
        $this->stream->shouldReceive('tell')
            ->once()
            ->andReturn($initialPosition = 0);

        $this->matcherInterface->shouldReceive('match')
            ->with($this->sut)
            ->once()
            ->andReturn(null);

        $this->stream->shouldReceive('seek')
            ->once()
            ->with($initialPosition);

        $result = $this->sut->tryMatchWith($this->matcherInterface);

        $this->assertNull($result);

        $this->assertEquals(1, $this->sut->getPosition()->getLine());

        $this->assertEquals(1, $this->sut->getPosition()->getColumn());
    }

    /**
     * @test
     * @dataProvider provider_boolean
     * @param bool $isEOF
     */
    public function it_is_done_matching_when_the_stream_is_finished($isEOF)
    {
        $this->stream->shouldReceive('eof')
            ->once()
            ->andReturn($isEOF);

        $this->assertEquals($isEOF, $this->sut->isDoneMatching());
    }

    public function provider_boolean()
    {
        return [
            [true],
            [false],
        ];
    }
}
