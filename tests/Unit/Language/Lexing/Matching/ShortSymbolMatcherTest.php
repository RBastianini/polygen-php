<?php

namespace Tests\Polygen\Unit\Language\Lexing\Matching;

use Polygen\Language\Lexing\Matching\ShortSymbolMatcher;
use Polygen\Language\Token\Token;
use Tests\StreamUtils;

class ShortSymbolMatcherTest extends \PHPUnit_Framework_TestCase
{
    use StreamUtils;

    /**
     * @test
     * @dataProvider short_symbols_matcher_provider
     * @param string $source
     * @param Token $expectedMatch
     */
    public function it_can_match_short_symbols($source, $expectedMatch)
    {
        $SUT = new ShortSymbolMatcher($this->given_a_source_stream($source));
        $result = $SUT->next();
        $this->assertEquals($expectedMatch, $result);
    }

    /**
     * @return array
     */
    public function short_symbols_matcher_provider()
    {
        return [
            ['(', Token::leftBracket()],
            [')', Token::rightBracket()],
            ['[', Token::leftSquareBracket()],
            [']', Token::rightSquareBracket()],
            ['{', Token::leftCurlyBracket()],
            ['}', Token::rightCurlyBracket()],
            ['_', Token::underscore()],
            [';', Token::semicolon()],
            ['|', Token::pipe()],
            ['>', Token::unfolding()],
            ['<', Token::folding()],
            ['*', Token::star()],
            ['+', Token::plus()],
            ['-', Token::minus()],
            [',', Token::comma()],
            ['^', Token::cap()],
            ['.', Token::dot()],
            ['/', Token::slash()],
        ];
    }

    /**
     * @test
     * @dataProvider other_symbols_matcher_provider
     * @param string $source
     */
    public function it_does_not_match_other_symbols($source)
    {
        $SUT = new ShortSymbolMatcher($this->given_a_source_stream($source));
        $result = $SUT->next();
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function other_symbols_matcher_provider()
    {
        return [
            ['@'],
            ['#'],
            ['?'],
            ['!'],
        ];
    }
}
