<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Exceptions\Lexing\UnterminatedCommentException;
use Polygen\Language\Token\Token;

/**
 * Matcher for comment strings.
 */
class CommentMatcher extends BaseMatcher
{
    const COMMENT_START = '(*';
    const COMMENT_END = '*)';

    /**
     * @return Token
     */
    public function doMatch()
    {
        if ($this->read(2) != self::COMMENT_START) {
            return null;
        }
        $comment = '';
        do {
            $lastChar = $this->read();
            if ($lastChar === null) {
                throw new UnterminatedCommentException($this->tell());
            }
            $comment .= $lastChar;
        } while (substr($comment, -2) !== self::COMMENT_END);
        return Token::comment(substr($comment, 0, -2));
    }
}
