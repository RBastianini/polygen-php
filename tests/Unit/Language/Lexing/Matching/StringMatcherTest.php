<?php

namespace Tests\Polygen\Unit\Language\Lexing\Matching;

use Polygen\Language\Exceptions\InvalidEscapeSequenceException;
use Polygen\Language\Exceptions\UnterminatedStringException;
use Polygen\Language\Lexing\Matching\StringMatcher;
use Polygen\Language\Token\Token;
use Tests\StreamUtils;
use Tests\TestCase;

class StringMatcherTest extends TestCase
{
    use StreamUtils;

    /**
     * @test
     * @dataProvider string_provider
     * @param string $source
     * @param Token $expectedMatch
     */
    public function it_can_match_strings($source, $expectedMatch)
    {
        $SUT = new StringMatcher($this->given_a_source_stream($source));
        $result = $SUT->next();
        $this->assertEquals($expectedMatch, $result);
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
        $SUT = new StringMatcher($this->given_a_source_stream($source));
        $result = $SUT->next();
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
        $SUT = new StringMatcher($this->given_a_source_stream($source));
        $result = $SUT->next();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function escape_provider()
    {
        return [
            ['"\""', Token::terminatingSymbol('"')],
            ['"a\ttab"', Token::terminatingSymbol("a\ttab")],
            ['"a\ncarriage\rreturn"', Token::terminatingSymbol("a\ncarriage\rreturn")],
            ['"backspaces\bare\bskipped"', Token::terminatingSymbol('backspacesareskipped')],
            ['"ascii sequences \123are\125 converted"', Token::terminatingSymbol('ascii sequences {are} converted')],
        ];
    }

    /**
     * @test
     */
    public function it_throws_exceptions_on_unterminated_strings()
    {
        $this->expectException(UnterminatedStringException::class);
        $this->expectExceptionMessage("Syntax error at offset 1.");
        $SUT = new StringMatcher($this->given_a_source_stream('"look! What\'s tha'));
        $SUT->next();
    }

    /**
     * @test
     */
    public function it_throws_exceptions_on_unknown_escape_sequences()
    {
        $this->expectException(InvalidEscapeSequenceException::class);
        $this->expectExceptionMessage("Unknown escape sequence '\\g' at offset 17.");
        $SUT = new StringMatcher($this->given_a_source_stream('"I wonder what \g is?"'));
        $SUT->next();
    }

    /**
     * @test
     */
    public function it_throws_exceptions_on_unknown_ascii_codes()
    {
        $this->expectException(InvalidEscapeSequenceException::class);
        $this->expectExceptionMessage("Unknown escape sequence '\\256' at offset 19.");
        $SUT = new StringMatcher($this->given_a_source_stream("\"I wonder what \\256 is?\""));
        $SUT->next();
    }
}
