<?php

namespace Tests\Polygen\Unit\Language\Lexing\Matching;

use Polygen\Language\Lexing\Matching\WhitespaceMatcher;
use Polygen\Language\Token\Token;
use Tests\StreamUtils;

class WhitespaceMatcherTest extends \PHPUnit_Framework_TestCase
{
    use StreamUtils;

    /**
     * @test
     * @dataProvider whitespace_provider
     * @param string $source
     */
    public function it_can_match_whitespace_like_chars($source)
    {
        $stream = $this->given_a_source_stream($source);
        $SUT = new WhitespaceMatcher($stream);
        $result = $SUT->next();
        $this->assertEquals(Token::whitespace(), $result);
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
        $SUT = new WhitespaceMatcher($this->given_a_source_stream($source));
        $result = $SUT->next();
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
