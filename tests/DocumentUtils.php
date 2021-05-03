<?php

namespace Tests;

use GuzzleHttp\Stream\StreamInterface;
use Polygen\Language\Document;
use Polygen\Language\Lexing\Lexer;
use Polygen\Language\Parsing\DocumentParser;
use Polygen\Stream\CachingStream;
use Polygen\Stream\LexingStreamWrapper;
use Polygen\Stream\SavePointStream;
use Polygen\Stream\TokenStream;

/**
 * Utility trait for tests that need a document parsed from a stream.
 */
trait DocumentUtils
{
    private function given_a_parser(StreamInterface $grammarStream)
    {
        return new DocumentParser(
            new SavePointStream(
                new CachingStream(
                    new TokenStream(
                        new Lexer(
                            new LexingStreamWrapper(
                                $grammarStream
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * @param StreamInterface $grammarStream
     * @return Document
     */
    private function given_a_document(StreamInterface $grammarStream)
    {
        return $this->given_a_parser($grammarStream)->parse();
    }
}
