<?php

namespace Polygen\Language\Lexing;

use GuzzleHttp\Stream\StreamInterface;
use Polygen\Language\Exceptions\SyntaxErrorException;
use Polygen\Language\Lexing\Matching\CommentMatcher;
use Polygen\Language\Lexing\Matching\DefinitionSymbolMatcher;
use Polygen\Language\Lexing\Matching\DotLabelMatcher;
use Polygen\Language\Lexing\Matching\LongSymbolMatcher;
use Polygen\Language\Lexing\Matching\MatcherInterface;
use Polygen\Language\Lexing\Matching\NonTerminatingSymbolMatcher;
use Polygen\Language\Lexing\Matching\ShortSymbolMatcher;
use Polygen\Language\Lexing\Matching\StringMatcher;
use Polygen\Language\Lexing\Matching\TerminatingSymbolMatcher;
use Polygen\Language\Lexing\Matching\WhitespaceMatcher;

/**
 * Lexer class.
 * Given a text stream it attempts to consume it translating it into a stream of tokens.
 */
class Lexer
{
    /**
     * @var StreamInterface
     */
    private $source;

    /**
     * Lexer constructor.
     *
     * @param StreamInterface $source
     */
    public function __construct(StreamInterface $source)
    {
        $this->source = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokens()
    {
        $matchers = $this->initMatchers();
        while (!$this->source->eof()) {
            $matched = false;
            foreach ($matchers as $matcher) {
                $token = $matcher->next();
                if ($token) {
                    $matched = true;
                    yield $token;
                    break;
                }
            }
            if (!$matched && !$this->source->eof()) {
                $t = $this->source->tell();
                throw new SyntaxErrorException($t);
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
                return new $matcherName($this->source);
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
            ]
        );
    }
}
