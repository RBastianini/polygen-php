<?php

namespace Tests\Unit\Language\Lexing\Matching;

use Polygen\Language\Exceptions\Lexing\UnterminatedCommentException;
use Polygen\Language\Lexing\Matching\CommentMatcher;
use Polygen\Language\Token\Token;
use Tests\StreamUtils;
use Tests\TestCase;

class CommentMatcherTest extends TestCase
{
    use StreamUtils;

    /**
     * @test
     * @dataProvider valid_comment_string_provider
     * @param string $commentString
     * @param string $expectedValue
     */
    public function it_can_parse_a_comment_string($commentString, $expectedValue)
    {
        $SUT = new CommentMatcher($this->given_a_source_stream($commentString));
        $result = $SUT->next();
        $this->assertInstanceOf(Token::class, $result);
        $this->assertEquals(Token::comment($expectedValue), $result);
    }

    /**
     * @return array
     */
    public function valid_comment_string_provider()
    {
        return [
            ['(*this is a comment*)', 'this is a comment'],
            ['(* this is another comment *)', ' this is another comment '],
            ['(**)', ''],
            ["(*\nthis is a\nmulti-line comment\n*)", "\nthis is a\nmulti-line comment\n"]
        ];
    }

    /**
     * @test
     * @dataProvider invalid_comment_string_provider
     * @param string $nonCommentString
     */
    public function it_does_not_parse_other_tokens($nonCommentString)
    {
        $SUT = new CommentMatcher($this->given_a_source_stream($nonCommentString));
        $result = $SUT->next();
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function invalid_comment_string_provider()
    {
        return [
            ['( * this is not a comment*)'],
            [')* this was almost a comment *)'],
            ['definitely not a comment']
        ];
    }

    /**
     * @test
     */
    public function it_throws_an_exception_on_unterminated_comments()
    {
        $SUT = new CommentMatcher($this->given_a_source_stream('(*'));
        $this->expectException(UnterminatedCommentException::class);
        $this->expectExceptionMessage("Syntax error at offset 2.");
        $SUT->next();
    }
}
