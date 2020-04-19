<?php

namespace Tests\Polygen\Integration\Stream;

use Mockery;
use Polygen\Language\Lexing\Lexer;
use Polygen\Language\Token\Token;
use Polygen\Stream\CachingStream;
use Polygen\Stream\TokenStream;
use Polygen\Stream\TokenStreamInterface;
use Tests\TestCase;

class CachingStreamTest extends TestCase
{
    /**
     * @test
     */
    public function it_knows_when_it_is_at_the_end_of_the_stream_if_it_never_read_it()
    {
        $subject = new CachingStream($this->given_a_token_stream([]));
        $this->assertTrue($subject->isEOF());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_seeking_past_the_end_of_the_cache()
    {
        $subject = new CachingStream(
            $this->given_a_token_stream(
                [
                    Token::terminatingSymbol('1'),
                    Token::terminatingSymbol('2'),
                    Token::terminatingSymbol('3'),
                ]
            )
        );
        $this->expectExceptionMessage('Can\'t seek ahead of the cache.');
        $subject->seek(2);
    }

    public function it_throws_an_exception_when_advancing_past_the_end_of_the_stream()
    {
        $subject = new CachingStream(
            $this->given_a_token_stream(
                [
                    Token::terminatingSymbol('1'),
                ]
            )
        );
        $subject->advance();
        $this->expectExceptionMessage('Trying to read past the end of the stream.');
        $subject->advance();
    }

    /**
     * @test
     */
    public function it_does_not_advance_when_calling_nextToken()
    {
        $subject = new CachingStream(
            $this->given_a_token_stream(
                [
                    Token::terminatingSymbol('1'),
                    Token::terminatingSymbol('2'),
                    Token::terminatingSymbol('3'),
                ]
            )
        );
        $this->assertEquals(Token::terminatingSymbol('1'), $subject->nextToken());
        $this->assertEquals(Token::terminatingSymbol('1'), $subject->nextToken());
        $this->assertEquals(Token::terminatingSymbol('1'), $subject->nextToken());
        $subject->advance();
        $this->assertEquals(Token::terminatingSymbol('2'), $subject->nextToken());

    }

    /**
     * @test
     */
    public function it_knows_when_it_is_at_the_end_of_the_stream_when_it_gets_there()
    {
        $subject = new CachingStream(
            $this->given_a_token_stream(
                [
                    Token::terminatingSymbol('blah'),
                    Token::terminatingSymbol('blah'),
                    Token::terminatingSymbol('blah'),
                ]
            )
        );
        $this->assertFalse($subject->isEOF());
        $subject->advance();
        $this->assertFalse($subject->isEOF());
        $subject->advance();
        $this->assertTrue($subject->isEOF());
    }

    /**
     * @test
     */
    public function it_can_advance()
    {
        $subject = new CachingStream(
            $this->given_a_token_stream(
                [
                    Token::terminatingSymbol('1'),
                    Token::terminatingSymbol('2'),
                    Token::terminatingSymbol('3'),
                ]
            )
        );

        $subject->advance();
        $this->assertFalse($subject->isEOF());
        $subject->advance();
        $this->assertEquals(Token::terminatingSymbol('3'), $subject->nextToken());
        $this->assertTrue($subject->isEOF());
    }

    /**
     * @test
     */
    public function it_knows_when_it_is_at_the_end_of_the_stream_after_rewinding()
    {

        $subject = new CachingStream(
            $this->given_a_token_stream(
                [
                    Token::terminatingSymbol('1'),
                    Token::terminatingSymbol('2'),
                    Token::terminatingSymbol('3'),
                ]
            )
        );

        $this->assertFalse($subject->isEOF());
        $subject->advance();
        $this->assertFalse($subject->isEOF());
        $subject->advance();
        $this->assertTrue($subject->isEOF());
        $subject->seek(2);
        $this->assertTrue($subject->isEOF());
        $subject->seek(1);
        $this->assertFalse($subject->isEOF());
        $subject->seek(0);
        $this->assertFalse($subject->isEOF());
        $subject->advance();
        $this->assertFalse($subject->isEOF());
        $subject->advance();
        $this->assertTrue($subject->isEOF());
    }

    /**
     * @test
     */
    public function it_knows_the_offset_it_is_at()
    {
        $subject = new CachingStream(
            $this->given_a_token_stream(
                [
                    Token::terminatingSymbol('1'),
                    Token::terminatingSymbol('2'),
                    Token::terminatingSymbol('3'),
                ]
            )
        );

        $this->assertEquals(0, $subject->tell());
        $subject->advance();
        $subject->nextToken();
        $subject->nextToken();
        $subject->nextToken();
        $this->assertEquals(1, $subject->tell());
        $subject->advance();
        $this->assertEquals(2, $subject->tell());
        $subject->seek(1);
        $this->assertEquals(1, $subject->tell());
    }

    /**
     * @test
     */
    public function it_does_not_allow_rewinding_before_the_start()
    {
        $this->expectExceptionMessage("Offset can't be negative.");
        $subject = new CachingStream($this->given_a_token_stream([Token::comment("")]));
        $subject->seek(-1);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_passed_a_non_integer_number_to_seek()
    {
        $this->expectExceptionMessage("Offset must be an integer.");
        $subject = new CachingStream($this->given_a_token_stream([Token::comment("")]));
        $subject->seek(0.2);
    }

    /**
     * @param Token[]
     * @return TokenStreamInterface
     */
    private function given_a_token_stream(array $tokens)
    {
        return new TokenStream(
            Mockery::mock(Lexer::class, ['getTokens' => new \ArrayIterator($tokens)])
        );
    }
}
