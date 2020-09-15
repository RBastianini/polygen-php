<?php

namespace Tests\Unit\Language\Lexing\Matching;

use Polygen\Language\Lexing\Matching\NonTerminatingSymbolMatcher;
use Polygen\Language\Token\Token;
use Tests\TestCase;
use Tests\Utils\MatcherInputHelper;

class NonTerminatingSymbolMatcherTest extends TestCase
{
    /**
     * @test
     * @dataProvider valid_non_terminating_symbol_provider
     * @param string $source
     * @param string $expectedValue
     */
    public function it_can_parse_a_comment_string($source, $expectedValue)
    {
        $SUT = new NonTerminatingSymbolMatcher();
        $result = $SUT->match(MatcherInputHelper::get($source));
        $this->assertEquals($expectedValue, $result->getToken());
    }

    /**
     * @return array
     */
    public function valid_non_terminating_symbol_provider()
    {
        return [
            ['A', Token::nonTerminatingSymbol('A')],
            ['A0', Token::nonTerminatingSymbol('A0')],
            ['A0BO', Token::nonTerminatingSymbol('A0BO')],
            ['ABOBO', Token::nonTerminatingSymbol('ABOBO')],
            ['Abbbb', Token::nonTerminatingSymbol('Abbbb')],
            ["A\n", Token::nonTerminatingSymbol("A")],
        ];
    }

    /**
     * @test
     * @dataProvider invalid_string_provider
     * @param string $source
     */
    public function it_does_not_parse_other_tokens($source)
    {
        $SUT = new NonTerminatingSymbolMatcher();
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
            ['this is a terminal symbol'],
            ['::=']
        ];
    }
}
