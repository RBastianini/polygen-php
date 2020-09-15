<?php

namespace Tests\Unit\Language\Lexing\Matching;

use Polygen\Language\Lexing\Matching\TerminatingSymbolMatcher;
use Polygen\Language\Token\Token;
use Tests\TestCase;
use Tests\Utils\MatcherInputHelper;

class TerminatingSymbolMatcherTest extends TestCase
{
    /**
     * @test
     * @dataProvider valid_terminating_symbol_provider
     * @param string $source
     * @param string $expectedValue
     */
    public function it_can_parse_a_comment_string($source, $expectedValue)
    {
        $SUT = new TerminatingSymbolMatcher();
        $result = $SUT->match(MatcherInputHelper::get($source));
        $this->assertEquals($expectedValue, $result->getToken());
    }

    /**
     * @return array
     */
    public function valid_terminating_symbol_provider()
    {
        return [
            ['a', Token::terminatingSymbol('a')],
            ['a0', Token::terminatingSymbol('a0')],
            ['a0bo', Token::terminatingSymbol('a0bo')],
            ['aBO', Token::terminatingSymbol('aBO')],
            ["'aBO'", Token::terminatingSymbol("'aBO'")],
            ['thisIsATerminalSymbol', Token::terminatingSymbol('thisIsATerminalSymbol')],
            ["thisIsATerminalSymbol\n", Token::terminatingSymbol("thisIsATerminalSymbol")]
        ];
    }

    /**
     * @test
     * @dataProvider invalid_string_provider
     * @param string $source
     */
    public function it_does_not_parse_other_tokens($source)
    {
        $SUT = new TerminatingSymbolMatcher();
        $result = $SUT->match(MatcherInputHelper::get($source));
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function invalid_string_provider()
    {
        return [
            ['(*this is a comment*)'],
            ['NonTerminalSymbol'],
            ['::='],
            [';'],
        ];
    }
}
