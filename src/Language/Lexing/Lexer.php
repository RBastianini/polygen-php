<?php

namespace Polygen\Language\Lexing;

use GuzzleHttp\Stream\StreamInterface;
use Polygen\Language\Exceptions\SyntaxErrorException;
use Polygen\Language\Lexing\Matching\CommentMatcher;
use Polygen\Language\Lexing\Matching\DefinitionSymbolMatcher;
use Polygen\Language\Lexing\Matching\DotLabelMatcher;
use Polygen\Language\Lexing\Matching\EndOfFileMatcher;
use Polygen\Language\Lexing\Matching\LongSymbolMatcher;
use Polygen\Language\Lexing\Matching\MatcherInterface;
use Polygen\Language\Lexing\Matching\MatchedToken;
use Polygen\Language\Lexing\Matching\NonTerminatingSymbolMatcher;
use Polygen\Language\Lexing\Matching\ShortSymbolMatcher;
use Polygen\Language\Lexing\Matching\StringMatcher;
use Polygen\Language\Lexing\Matching\TokenMatcher;
use Polygen\Language\Lexing\Matching\TerminatingSymbolMatcher;
use Polygen\Language\Lexing\Matching\WhitespaceMatcher;

/**
 * Lexer class.
 * Given a text stream it attempts to consume it translating it into a stream of tokens.
 */
class Lexer
{
    /**
     * @var TokenMatcher
     */
    private $tentativeMatcher;

    /**
     * Lexer constructor.
     *
     * @param StreamInterface $tentativeMatcher
     */
    public function __construct(TokenMatcher $tentativeMatcher)
    {
        $this->tentativeMatcher = $tentativeMatcher;
    }

    /**
     * Reads and returns the next token.
     *
     * @return MatchedToken|\Generator
     */
    public function getTokens()
    {
        $matchers = $this->initMatchers();
        while (!$this->tentativeMatcher->isDoneMatching()) {
            $matched = false;
            foreach ($matchers as $matcher) {
                $matchingResult = $this->tentativeMatcher->tryMatchWith($matcher);
                if ($matchingResult) {
                    $matched = true;
                    yield $matchingResult;
                    break;
                }
            }
            if (!$matched && !$this->tentativeMatcher->isDoneMatching()) {
                throw SyntaxErrorException::atPosition($this->tentativeMatcher->getPosition());
            }
        }
    }

    /**
     * Creates matchers for the stream that is being read.
     *
     * @return MatcherInterface[]
     */
    private function initMatchers()
    {
        return array_map(
            function ($matcherName)  {
                return new $matcherName();
            },
            [
                WhitespaceMatcher::class,
                DefinitionSymbolMatcher::class,
                LongSymbolMatcher::class,
                DotLabelMatcher::class,
                CommentMatcher::class,
                ShortSymbolMatcher::class,
                TerminatingSymbolMatcher::class,
                NonTerminatingSymbolMatcher::class,
                StringMatcher::class,
                EndOfFileMatcher::class,
            ]
        );
    }
}
