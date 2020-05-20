<?php

namespace Polygen;

use GuzzleHttp\Stream\StreamInterface;
use Polygen\Language\Document;
use Polygen\Language\Exceptions\StaticCheckException;
use Polygen\Language\Interpretation\Context;
use Polygen\Language\Interpretation\Interpreter;
use Polygen\Language\Lexing\Lexer;
use Polygen\Language\Parsing\DocumentParser;
use Polygen\Language\Preprocessing\AbstractToConcreteSyntaxConverter;
use Polygen\Language\Preprocessing\StaticChecker;
use Polygen\Stream\CachingStream;
use Polygen\Stream\SavePointStream;
use Polygen\Stream\TokenStream;

/**
 * Utility class to get a validated and converted document, and to parse it.
 */
class Polygen
{
    /**
     * Reads the stream and returns a validated Polygen document in concrete syntax.
     * @param string $startSymbol
     * @return Document
     */
    public function getDocument(StreamInterface $grammarStream, $startSymbol = Document::START)
    {
        $parser = new DocumentParser(
            new SavePointStream(
                new CachingStream(
                    new TokenStream(
                        new Lexer(
                            $grammarStream
                        )
                    )
                )
            )
        );

        $rawDocument = $parser->parse();

        $errorCollection = StaticChecker::get($startSymbol)->check($rawDocument);

        if (!$errorCollection->isEmpty()) {
            throw new StaticCheckException($errorCollection);
        }

        $convertedDocument = AbstractToConcreteSyntaxConverter::create()->convert($rawDocument);

        return $convertedDocument;
    }

    /**
     * Generates a sentence from the grammar contained in the passed document.
     *
     * @param Context|null $context
     * @return string
     */
    public function generate(Document $document, $context = null)
    {
        return (new Interpreter())->interpret($document, $context ?: Context::get());
    }
}
