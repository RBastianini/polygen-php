<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;

/**
 * Matches a definition symbol.
 *
 * This is the only three character symbol, that's why it has a dedicated matcher instead of using one of the
 * Short or Long SymbolMatcher.
 */
class DefinitionSymbolMatcher implements MatcherInterface
{
    const DEFINITION_SYMBOL = '::=';

    /**
     * @return MatchedToken|null
     */
    public function match(MatcherInput $streamWrapper)
    {
        $string = $streamWrapper->read(3);
        if (strlen($string) < 3) {
            return null;
        }
        return $string === self::DEFINITION_SYMBOL
            ? new MatchedToken(Token::definition(), $streamWrapper->getPosition())
            : null;
    }
}
