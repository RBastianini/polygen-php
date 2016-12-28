<?php

namespace Tests\Polygen\Unit\Language\Lexing\Matching;

use Polygen\Language\Lexing\Matching\DotLabelMatcher;
use Polygen\Language\Token\Token;
use Tests\StreamUtils;

class DotLabelMatcherMatcherTest extends \PHPUnit_Framework_TestCase
{
    use StreamUtils;

    /**
     * @test
     * @dataProvider dot_label_matcher_provider
     * @param string $source
     * @param Token $expectedMatch
     */
    public function it_can_match_long_symbols($source, $expectedMatch)
    {
        $SUT = new DotLabelMatcher($this->given_a_source_stream($source));
        $result = $SUT->next();
        $this->assertEquals($expectedMatch, $result);
    }

    /**
     * @return array
     */
    public function dot_label_matcher_provider()
    {
        return [
            ['.Hey', Token::dotLabel('Hey')],
            ['.L', Token::dotLabel('L')],
            ['.L123', Token::dotLabel('L123')],
            [".L123\n", Token::dotLabel('L123')],
        ];
    }

    /**
     * @test
     * @dataProvider other_symbols_matcher_provider
     * @param string $source
     */
    public function it_does_not_match_other_symbols($source)
    {
        $SUT = new DotLabelMatcher($this->given_a_source_stream($source));
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
}
