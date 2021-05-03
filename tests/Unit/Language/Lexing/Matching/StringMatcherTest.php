<?php

namespace Tests\Polygen\Unit\Language\Lexing\Matching;

use Polygen\Language\Exceptions\SyntaxErrorException;
use Polygen\Language\Lexing\Matching\StringMatcher;
use Polygen\Language\Token\Token;
use Tests\TestCase;
use Tests\Utils\MatcherInputHelper;

class StringMatcherTest extends TestCase
{
    /**
     * @test
     * @dataProvider string_provider
     * @param string $source
     * @param Token $expectedMatch
     */
    public function it_can_match_strings($source, $expectedMatch)
    {
        $SUT = new StringMatcher();
        $result = $SUT->match(MatcherInputHelper::get($source));
        $this->assertEquals($expectedMatch, $result->getToken());
    }

    /**
     * @return array
     */
    public function string_provider()
    {
        return [
            ['""', Token::terminatingSymbol('')],
            ['"a"', Token::terminatingSymbol('a')],
            ['" a string with spaces "', Token::terminatingSymbol(' a string with spaces ')],
            ["\"a string with\n\ncarriage returns\"", Token::terminatingSymbol("a string with\n\ncarriage returns")],
        ];
    }

    /**
     * @test
     * @dataProvider other_symbols_matcher_provider
     * @param string $source
     */
    public function it_does_not_match_other_symbols($source)
    {
        $SUT = new StringMatcher();
        $result = $SUT->match(MatcherInputHelper::get($source));
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function other_symbols_matcher_provider()
    {
        return [
            ['.('],
            ['.nope'],
            ['...'],
        ];
    }

    /**
     * @test
     * @dataProvider escape_provider
     * @param string $source
     * @param Token $expected
     */
    public function it_escapes_characters($source, $expected)
    {
        $SUT = new StringMatcher();
        $result = $SUT->match(MatcherInputHelper::get($source));
        $this->assertEquals($expected, $result->getToken());
    }

    /**
     * @return array
     */
    public function escape_provider()
    {
        return [
            ['"\""', Token::terminatingSymbol('"')],
            ['"a\ttab"', Token::terminatingSymbol("a\ttab")],
            ['"a\ncarriage\rreturn"', Token::terminatingSymbol('a' . PHP_EOL . "carriage\rreturn")],
            ['"backspaces\bare\bskipped"', Token::terminatingSymbol('backspacesareskipped')],
            ['"ascii sequences \123are\125 converted"', Token::terminatingSymbol('ascii sequences {are} converted')],
        ];
    }

    /**
     * @test
     */
    public function it_throws_exceptions_on_unterminated_strings()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage("Unterminated string at line 1 and column 1.");
        $SUT = new StringMatcher();
        $SUT->match(MatcherInputHelper::get('"look! What\'s tha'));
    }

    /**
     * @test
     */
    public function it_throws_exceptions_on_unknown_escape_sequences()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage("Invalid escape sequence '\\g' at line 1 and column 17.");
        $SUT = new StringMatcher();
        $SUT->match(MatcherInputHelper::get('"I wonder what \g is?"'));
    }

    /**
     * @test
     */
    public function it_throws_exceptions_on_unknown_ascii_codes()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage("Invalid escape sequence '\\256' at line 1 and column 19.");
        $SUT = new StringMatcher();
        $SUT->match(MatcherInputHelper::get("\"I wonder what \\256 is?\""));
    }
}
