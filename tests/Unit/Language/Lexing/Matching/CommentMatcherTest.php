<?php

namespace Tests\Unit\Language\Lexing\Matching;

use Polygen\Language\Exceptions\SyntaxErrorException;
use Polygen\Language\Lexing\Matching\CommentMatcher;
use Polygen\Language\Lexing\Matching\MatchedToken;
use Polygen\Language\Token\Token;
use Tests\TestCase;
use Tests\Utils\MatcherInputHelper;

class CommentMatcherTest extends TestCase
{
    /**
     * @test
     * @dataProvider valid_comment_string_provider
     * @param string $commentString
     * @param string $expectedValue
     */
    public function it_can_parse_a_comment_string($commentString, $expectedValue)
    {
        $SUT = new CommentMatcher();
        $result = $SUT->match(MatcherInputHelper::get($commentString));
        $this->assertInstanceOf(MatchedToken::class, $result);
        $this->assertEquals(Token::comment($expectedValue), $result->getToken());
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
        $SUT = new CommentMatcher();
        $result = $SUT->match(MatcherInputHelper::get($nonCommentString));
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
        $SUT = new CommentMatcher();
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage("Unterminated comment at line 1 and column 2.");
        $SUT->match(MatcherInputHelper::get('(*'));
    }
}
