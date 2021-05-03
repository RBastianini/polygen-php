<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;

/**
 * Matches dot labels.
 */
class DotLabelMatcher implements MatcherInterface
{
    use RegexMatcherTrait;

    const REGEX = '{^[A-Za-z0-9]*$}D';

    /**
     * @return MatchedToken|null
     */
    public function match(MatcherInput $lexingStreamWrapper)
    {
        if ($lexingStreamWrapper->read() != '.') {
            return null;
        }
        $label = '';
        while ($this->matchesRegex($label . $lexingStreamWrapper->peek())) {
            $lastChar = $lexingStreamWrapper->read();
            if ($lastChar === null) {
                break;
            }
            $label .= $lastChar;
        }
        return strlen($label)
            ? new MatchedToken(Token::dotLabel($label), $lexingStreamWrapper->getPosition())
            : null;
    }
}
