<?php

namespace Tests\Polygen\Unit\Language\Lexing\Matching;

use Polygen\Language\Lexing\Matching\DefinitionSymbolMatcher;
use Polygen\Language\Lexing\Matching\MatchedToken;
use Polygen\Language\Token\Token;
use Tests\TestCase;
use Tests\Utils\MatcherInputHelper;

class DefinitionSymbolMatcherTest extends TestCase
{
    /**
     * @test
     */
    public function it_matches_the_definition_symbol()
    {
        $SUT = new DefinitionSymbolMatcher();
        $result = $SUT->match(MatcherInputHelper::get('::='));
        $this->assertInstanceOf(MatchedToken::class, $result);
        $this->assertEquals(Token::definition(), $result->getToken());
    }

    /**
     * @test
     * @dataProvider not_a_definition_symbol_provider
     * @param $string
     */
    public function it_does_not_match_other_strings($string)
    {
        $SUT = new DefinitionSymbolMatcher();
        $result = $SUT->match(MatcherInputHelper::get($string));
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function not_a_definition_symbol_provider()
    {
        return [
            [': : ='],
            [':: ='],
            [' ::='],
            [':='],
        ];
    }
}
