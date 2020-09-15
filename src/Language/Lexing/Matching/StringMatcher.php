<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Exceptions\SyntaxErrorException;
use Polygen\Language\Token\Token;

/**
 * Matches strings and converts them to terminating symbols.
 */
class StringMatcher implements MatcherInterface
{
    const DELIMITER = '"';
    const ESCAPE_CHAR = '\\';

    /**
     * @return MatchedToken|null
     */
    public function match(MatcherInput $streamWrapper)
    {
        $text = '';
        if ($streamWrapper->read() !== self::DELIMITER) {
            return null;
        }
        $stringStart = $streamWrapper->getPosition();
        $escapeNext = false;
        do {
            $isEscaped = false;
            if ($escapeNext) {
                $isEscaped = true;
                $escapeNext = false;
            }
            $char = $streamWrapper->read();
            if ($char === null) {
                throw SyntaxErrorException::unterminatedString($stringStart);
            }
            if ($isEscaped) {
                $text .= $this->escape($char, $streamWrapper);
            } elseif ($char !== self::ESCAPE_CHAR) {
                $text .= $char;
            }
            if ($char == self::ESCAPE_CHAR && !$isEscaped) {
                $escapeNext = true;
            }
        } while ($isEscaped || $char !== self::DELIMITER);
        return new MatchedToken(
            Token::terminatingSymbol(substr($text, 0, -1)),
            $streamWrapper->getPosition()
        );
    }

    /**
     * Converts escape sequence.
     * This method might consume more than one character from the stream if the first passed character was a digit
     * to support converting \numeric escape sequences to ASCII characters.
     *
     * @param string $char
     * @return string
     */
    private function escape($char, MatcherInput $streamWrapper)
    {
        switch ($char) {
            case '\\':
                return '\\';
            case 'n':
                return PHP_EOL;
            case 'b':
                // Quoted backspace character is not supported. Silently ignore that.
                return '';
            case 'r':
                return "\r";
            case 't':
                return "\t";
            case '"':
                return '"';
        }
        if ((string)(int) $char === $char) {
            // Numbers might have more than one digit, try to eat as many as we can find
            while (($nextChar = $streamWrapper->peek()) && (string)(int) $nextChar === $nextChar) {
                $char .= $streamWrapper->read();
            }
            $char = (int) $char;
            if ($char > 255 || $char < 0) {
                throw SyntaxErrorException::invalidEscapeSequence("\\$char", $streamWrapper->getPosition());
            }
            return chr($char);
        }
        throw SyntaxErrorException::invalidEscapeSequence("\\$char", $streamWrapper->getPosition());
    }
}
