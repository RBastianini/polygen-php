<?php

namespace Tests\Polygen\Unit\Language\Lexing\Matching;

use Polygen\Language\Lexing\Matching\DefinitionSymbolMatcher;
use Polygen\Language\Token\Token;
use Tests\StreamUtils;
use Tests\TestCase;

class DefinitionSymbolMatcherTest extends TestCase
{
    use StreamUtils;

    /**
     * @test
     */
    public function it_matches_the_definition_symbol()
    {
        $SUT = new DefinitionSymbolMatcher($this->given_a_source_stream('::='));
        $result = $SUT->next();
        $this->assertEquals(Token::definition(), $result);
    }

    /**
     * @test
     * @dataProvider not_a_definition_symbol_provider
     * @param $string
     */
    public function it_does_not_match_other_strings($string)
    {
        $SUT = new DefinitionSymbolMatcher($this->given_a_source_stream($string));
        $result = $SUT->next();
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
