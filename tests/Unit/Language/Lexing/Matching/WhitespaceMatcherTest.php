<?php

namespace Tests\Polygen\Unit\Language\Lexing\Matching;

use Polygen\Language\Lexing\Matching\WhitespaceMatcher;
use Polygen\Language\Token\Token;
use Tests\TestCase;
use Tests\Utils\MatcherInputHelper;

class WhitespaceMatcherTest extends TestCase
{
    /**
     * @test
     * @dataProvider whitespace_provider
     * @param string $source
     */
    public function it_can_match_whitespace_like_chars($source)
    {
        $SUT = new WhitespaceMatcher();
        $result = $SUT->match(MatcherInputHelper::get($source));
        $this->assertEquals(Token::whitespace(), $result->getToken());
    }

    /**
     * @return array
     */
    public function whitespace_provider()
    {
        return [
            [' '],
            ["\t"],
            ["\r"],
            ["\r\n"],
            ["\t\t  \r\n"], // Multiple whitespaces are matched together
        ];
    }

    /**
     * @test
     * @dataProvider non_whitespace_provider
     * @param string $source
     */
    public function it_does_not_match_other_symbols($source)
    {
        $SUT = new WhitespaceMatcher();
        $result = $SUT->match(MatcherInputHelper::get($source));
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function non_whitespace_provider()
    {
        return [
            ['a'],
            ['B'],
            ['::='],
            ['.'],
            [')'],
            [''],
        ];
    }
}
