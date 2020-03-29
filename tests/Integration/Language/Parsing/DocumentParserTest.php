<?php

namespace Tests\Integration\Language\Parsing;

use GuzzleHttp\Stream\Stream;
use PHPUnit\Framework\TestCase;
use Polygen\Document;
use Polygen\Language\Lexing\Lexer;
use Polygen\Language\Parsing\DocumentParser;
use Polygen\Stream\CachingStream;
use Polygen\Stream\SavePointStream;
use Polygen\Stream\TokenStream;

/**
 *
 */
class DocumentParserTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_parse_a_valid_file_without_blowing_up()
    {
        $subject = $this->given_a_parser(
            Stream::factory(
                fopen(__DIR__ . '/../../../files/incredible-commit.grm', 'r')
            )
        );
        $document = $subject->parse();

        $this->assertInstanceOf(Document::class, $document);
    }

    // TODO: for every new parsing bug, remember to add a dedicated test.

    private function given_a_parser(Stream $grammarStream)
    {
        return new DocumentParser(
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
    }
}
