<?php

namespace Tests\Polygen\Unit\Stream;

use Mockery;
use Polygen\Language\Lexing\Lexer;
use Polygen\Language\Token\Token;
use Polygen\Stream\TokenStream;

class TokenStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_skips_whitespaces_and_comments()
    {
        $tokens = [
            $firstToken = Token::leftBracket(),
            Token::whitespace(),
            Token::whitespace(),
            Token::comment('blah'),
            Token::whitespace(),
            $secondToken = Token::rightBracket(),
            Token::endOfFile(),
        ];
        $subject = new TokenStream($this->given_a_lexer($tokens));

        $this->assertEquals($firstToken, $subject->nextToken());
        $subject->advance();
        $this->assertEquals($secondToken, $subject->nextToken());
    }

    /**
     * @test
     */
    public function it_knows_when_it_is_at_the_end_of_the_file()
    {
        $tokens = [
            Token::comma(),
            Token::endOfFile(),
        ];
        $subject = new TokenStream($this->given_a_lexer($tokens));

        $this->assertfalse($subject->isEOF());
        $subject->advance();
        $this->assertfalse($subject->isEOF());
        $subject->advance();
        $this->assertTrue($subject->isEOF());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_trying_to_read_past_the_end_of_the_file()
    {
        $tokens = [
            Token::endOfFile(),
        ];
        $subject = new TokenStream($this->given_a_lexer($tokens));

        $this->assertFalse($subject->isEOF());
        $subject->advance();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Trying to read past the end of file.');

        $subject->advance();
    }


    /**
     * @param Token[] $tokens
     * @return Lexer
     */
    private function given_a_lexer(array $tokens)
    {
        return Mockery::mock(Lexer::class, ['getTokens' => new \ArrayIterator($tokens)]);
    }
}
