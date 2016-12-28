<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;

/**
 * Matches a definition symbol.
 *
 * This is the only three character symbol, that's why it has a dedicated matcher instead of using one of the
 * Short or Long SymbolMatcher.
 */
class DefinitionSymbolMatcher extends BaseMatcher
{
    const REGEX = '{::=}';

    /**
     * {@inheritdoc}
     */
    protected function doMatch()
    {
        $string = $this->read(3);
        if (strlen($string) < 3) {
            return null;
        }
        return $this->matchesRegex($string) ? Token::definition() : null;
    }
}
