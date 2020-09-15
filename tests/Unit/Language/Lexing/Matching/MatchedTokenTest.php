<?php

namespace Tests\Polygen\Unit\Language\Lexing\Matching;

use Mockery;
use Mockery\MockInterface;
use Polygen\Language\Lexing\Matching\MatchedToken;
use Polygen\Language\Lexing\Position;
use Polygen\Language\Token\Token;
use Tests\TestCase;

class MatchedTokenTest extends TestCase
{

    /**
     * @test
     */
    public function it_returns_the_token_that_was_matched()
    {
        $token = Token::comma();
        $sut = new MatchedToken($token, $this->given_a_position());

        $this->assertEquals($token, $sut->getToken());
    }

    /**
     * @test
     */
    public function it_returns_the_position_where_the_token_was_found()
    {
        $position = $this->given_a_position();
        $sut = new MatchedToken(Token::dot(), $position);

        $this->assertEquals($position, $sut->getPosition());
    }

    /**
     * @return Position|MockInterface
     */
    private function given_a_position()
    {
        return Mockery::mock(Position::class);
    }
}
