<?php

namespace Polygen\Language\Lexing\Matching;

trait RegexMatcherTrait
{
    /**
     * Utility method to check if a string matches a regex stored in on of the class' constants.
     *
     * @param string $string
     * @param string $regexName
     * @return bool
     */
    private function matchesRegex($string, $regexName = 'REGEX')
    {
        return preg_match(constant(static::class . "::$regexName"), $string) === 1;
    }
}
