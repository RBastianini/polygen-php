<?php

namespace Tests\Integration\Language\Parsing;

use PHPUnit\Framework\TestCase;
use Polygen\Document;
use Tests\DocumentUtils;
use Tests\StreamUtils;

class DocumentParserTest extends TestCase
{
    use DocumentUtils;
    use StreamUtils;

    /**
     * @test
     */
    public function it_can_parse_a_valid_file_without_blowing_up()
    {
        $subject = $this->given_a_parser(
            $this->given_a_source_file(__DIR__ . '/../../../files/incredible-commit.grm')
        );
        $document = $subject->parse();

        $this->assertInstanceOf(Document::class, $document);
    }


    /**
     * @test
     */
    public function it_parses_differently_atoms_interlevead_by_a_space_and_interleaved_by_a_comma()
    {
        $subject = $this->given_a_parser(
            $this->given_a_source_stream(
                <<< GRAMMAR
                A ::= test1, test2 and 3, test4 and 5, test6;
                B ::= test1 test2 and 3 test4 and 5 test6;
GRAMMAR
            )
        );
        $document = $subject->parse();

        $this->assertNotEquals(
            $document->getDefinition('A')->getProductions(),
            $document->getDefinition('B')->getProductions()
        );
    }

    // TODO: for every new parsing bug, remember to add a dedicated test.
}
