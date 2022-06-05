<?php

namespace Tests\Polygen\Unit\Language\Lexing\Matching;

use Mockery;
use Mockery\Mock;
use Polygen\Language\Lexing\Matching\ShortSymbolMatcher;
use Polygen\Language\Token\Token;
use Tests\TestCase;
use Tests\Utils\MatcherInputHelper;

class ShortSymbolMatcherTest extends TestCase
{
    /**
     * @test
     * @dataProvider short_symbols_matcher_provider
     * @param string $source
     * @param Token $expectedMatch
     */
    public function it_can_match_short_symbols($source, $expectedMatch)
    {
        $SUT = new ShortSymbolMatcher();
        $result = $SUT->match(MatcherInputHelper::get($source));
        $this->assertEquals($expectedMatch, $result->getToken());
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
        $SUT = new ShortSymbolMatcher();
        $result = $SUT->match(MatcherInputHelper::get($source));
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_input_is_null() {
        $SUT = new ShortSymbolMatcher();
        $matcherMock = Mockery::mock(MatcherInputHelper::class);
        $matcherMock->expects('read')
            ->once()
            ->andReturn(null);
        $result = $SUT->match($matcherMock);
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
