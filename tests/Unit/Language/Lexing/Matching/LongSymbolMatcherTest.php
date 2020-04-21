<?php

namespace Tests\Polygen\Unit\Language\Lexing\Matching;

use Polygen\Language\Lexing\Matching\LongSymbolMatcher;
use Polygen\Language\Token\Token;
use Tests\StreamUtils;
use Tests\TestCase;

class LongSymbolMatcherTest extends TestCase
{
    use StreamUtils;

    /**
     * @test
     * @dataProvider long_symbols_matcher_provider
     * @param string $source
     * @param Token $expectedMatch
     */
    public function it_can_match_long_symbols($source, $expectedMatch)
    {
        $SUT = new LongSymbolMatcher($this->given_a_source_stream($source));
        $result = $SUT->next();
        $this->assertEquals($expectedMatch, $result);
    }

    /**
     * @return array
     */
    public function long_symbols_matcher_provider()
    {
        return [
            [':=', Token::assignment()],
            ['>>', Token::leftDeepUnfolding()],
            ['<<', Token::rightDeepUnfolding()],
            ['.(', Token::leftDotBracket()],
        ];
    }

    /**
     * @test
     * @dataProvider other_symbols_matcher_provider
     * @param string $source
     */
    public function it_does_not_match_other_symbols($source)
    {
        $SUT = new LongSymbolMatcher($this->given_a_source_stream($source));
        $result = $SUT->next();
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function other_symbols_matcher_provider()
    {
        return [
            ['<'],
            ['>'],
            [':>>'],
            ['>:>'],
            ['.)'],
            [')'],
        ];
    }
}
