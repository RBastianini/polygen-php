<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Token\Token;

/**
 * Matches dot labels.
 */
class DotLabelMatcher extends BaseMatcher
{
    const REGEX = '{^[A-Z][A-Za-z0-9]*$}D';

    /**
     * @return Token
     */
    public function doMatch()
    {
        if ($this->read() != '.') {
            return null;
        }
        $label = '';
        while ($this->matchesRegex($label . $this->peek())) {
            $lastChar = $this->read();
            if ($lastChar === null) {
                break;
            }
            $label .= $lastChar;
        }
        return strlen($label) ? Token::dotLabel($label) : null;
    }
}
