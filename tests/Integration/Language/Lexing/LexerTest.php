<?php

namespace Tests\Polygen\Integration\Language\Lexing;

use Polygen\Language\Exceptions\SyntaxErrorException;
use Polygen\Language\Lexing\Lexer;
use Polygen\Language\Token\Token;
use Tests\StreamUtils;

class LexerTest extends \PHPUnit_Framework_TestCase
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
        $SUT = new Lexer($this->given_a_source_stream($source));
        $this->assertEquals(
            array_merge($expectedTokens, [Token::endOfFile()]),
            iterator_to_array($SUT->getTokens())
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
                [Token::comment('just a comment')]
            ],
            [
                '(*a comment*)::=',
                [
                    Token::comment('a comment'),
                    Token::definition(),
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
        $this->expectExceptionMessage('Syntax error at offset 41.');
        $SUT = new Lexer($this->given_a_source_stream('This looked promising until THAT HAPPENED!'));
        // Consume all the stream (or at least try)
        iterator_to_array($SUT->getTokens());
    }
}
