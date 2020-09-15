<?php

namespace Polygen\Language\Lexing\Matching;

use Polygen\Language\Exceptions\SyntaxErrorException;
use Polygen\Language\Token\Token;

/**
 * Matcher for comment strings.
 */
class CommentMatcher implements MatcherInterface
{
    const COMMENT_START = '(*';
    const COMMENT_END = '*)';

    /**
     * @return MatchedToken|null
     */
    public function match(MatcherInput $input)
    {
        if ($input->read(2) != self::COMMENT_START) {
            return null;
        }
        $comment = '';
        do {
            $lastChar = $input->read();
            if ($lastChar === null) {
                throw SyntaxErrorException::unterminatedComment($input->getPosition());
            }
            $comment .= $lastChar;
        } while (substr($comment, -2) !== self::COMMENT_END);
        return new MatchedToken(
            Token::comment(substr($comment, 0, -2)),
            $input->getPosition()
        );
    }
}
