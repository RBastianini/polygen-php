<?php

namespace Tests\Polygen\Integration\Language\Lexing;

use Polygen\Language\Exceptions\SyntaxErrorException;
use Polygen\Language\Lexing\Lexer;
use Polygen\Language\Lexing\Matching\MatchedToken;
use Polygen\Language\Token\Token;
use Tests\StreamUtils;
use Tests\TestCase;

class LexerTest extends TestCase
{
    use StreamUtils;

    /**
     * @test
     * @dataProvider valid_stream_provider
     * @param $source
     * @param array $expectedTokens
     */
    public function it_can_lex_valid_files($source, array $expectedTokens)
    {
        $SUT = new Lexer($this->given_a_token_matcher($source));
        $tokens = array_map(
            function (MatchedToken  $matchedToken)
            {
                return $matchedToken->getToken();
            },
            iterator_to_array($SUT->getTokens())
        );
        $this->assertEquals(
            array_merge($expectedTokens, []),
            $tokens
        );
    }

    /**
     * @return array
     */
    public function valid_stream_provider()
    {
        return [
            [
                '(*just a comment*)',
                [
                    Token::comment('just a comment'),
                    Token::endOfFile(),
                ]
            ],
            [
                '(*a comment*)::=',
                [
                    Token::comment('a comment'),
                    Token::definition(),
                    Token::endOfFile(),
                ]
            ],
            [
                '(*this is an example grammar*)
                A ::= (b|c|{d|e|f|A}|_);',
                [
                    Token::comment('this is an example grammar'),
                    Token::whitespace(),
                    Token::nonTerminatingSymbol('A'),
                    Token::whitespace(),
                    Token::definition(),
                    Token::whitespace(),
                    Token::leftBracket(),
                    Token::terminatingSymbol('b'),
                    Token::pipe(),
                    Token::terminatingSymbol('c'),
                    Token::pipe(),
                    Token::leftCurlyBracket(),
                    Token::terminatingSymbol('d'),
                    Token::pipe(),
                    Token::terminatingSymbol('e'),
                    Token::pipe(),
                    Token::terminatingSymbol('f'),
                    Token::pipe(),
                    Token::nonTerminatingSymbol('A'),
                    Token::rightCurlyBracket(),
                    Token::pipe(),
                    Token::underscore(),
                    Token::rightBracket(),
                    Token::semicolon(),
                    Token::endOfFile(),
                ]
            ],
        ];
    }

    /**
     * @test
     */
    public function it_explodes_on_invalid_files()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Syntax error at line 1 and column 42.');
        $SUT = new Lexer($this->given_a_token_matcher('This looked promising until THAT HAPPENED!'));
        // Consume all the stream (or at least try)
        iterator_to_array($SUT->getTokens());
    }
}
