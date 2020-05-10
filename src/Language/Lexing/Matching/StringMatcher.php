<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Exceptions\Lexing\InvalidEscapeSequenceException;
use Polygen\Language\Exceptions\Lexing\UnterminatedStringException;
use Polygen\Language\Token\Token;

/**
 * Matches strings and converts them to terminating symbols.
 */
class StringMatcher extends BaseMatcher
{
    const DELIMITER = '"';
    const ESCAPE_CHAR = '\\';

    /**
     * @return Token
     */
    public function doMatch()
    {
        $text = '';
        if ($this->read() !== self::DELIMITER) {
            return null;
        }
        $stringStart = $this->tell();
        $escapeNext = false;
        do {
            $isEscaped = false;
            if ($escapeNext) {
                $isEscaped = true;
                $escapeNext = false;
            }
            $char = $this->read();
            if ($char === null) {
                throw new UnterminatedStringException($stringStart);
            }
            if ($isEscaped) {
                $text .= $this->escape($char);
            } elseif ($char !== self::ESCAPE_CHAR) {
                $text .= $char;
            }
            if ($char == self::ESCAPE_CHAR && !$isEscaped) {
                $escapeNext = true;
            }
        } while ($isEscaped || $char !== self::DELIMITER);
        return Token::terminatingSymbol(substr($text, 0, -1));
    }

    /**
     * Converts escape sequence.
     * This method might consume more than one character from the stream if the first passed character was a digit
     * to support converting \numeric escape sequences to ASCII characters.
     *
     * @param string $char
     * @return string
     */
    private function escape($char)
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
            while (($nextChar = $this->peek()) && (string)(int) $nextChar === $nextChar) {
                $char .= $this->read();
            }
            $char = (int) $char;
            if ($char > 255 || $char < 0) {
                throw new InvalidEscapeSequenceException("\\$char", $this->tell());
            }
            return chr($char);
        }
        throw new InvalidEscapeSequenceException("\\$char", $this->tell());
    }
}
