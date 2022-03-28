<?php

namespace Tests\Integration\Language\Parsing;

use Polygen\Grammar\Atom;
use Polygen\Grammar\FrequencyModifier;
use Polygen\Grammar\Label;
use Polygen\Grammar\LabelSelection;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\Document;
use Polygen\Language\Exceptions\Parsing\NoAtomFoundException;
use Polygen\Language\Exceptions\Parsing\UnexpectedTokenException;
use Polygen\Language\Token\Token;
use Tests\DocumentUtils;
use Tests\StreamUtils;
use Tests\TestCase;

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
    public function it_parses_differently_atoms_interleaved_by_a_space_and_interleaved_by_a_comma()
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
            $document->getDeclaration('A')->getProductionSet(),
            $document->getDeclaration('B')->getProductionSet()
        );
    }

    /**
     * @test
     */
    public function it_parses_mixed_plus_minus_modifiers()
    {
        $subject = $this->given_a_parser(
            $this->given_a_source_stream(
                <<< GRAMMAR
            A ::= C.(+-label1 | -label2 | +label3);
            B ::= +-term1 | -term2 | +term3;
GRAMMAR

            )
        );

        $document = $subject->parse();

        $expectedProductionA = new Production(
            new Sequence(
                [
                    Atom\AtomBuilder::get()->withUnfoldable(
                        UnfoldableBuilder::get()
                            ->withNonTerminatingToken(Token::nonTerminatingSymbol('C'))
                            ->build()
                    )->withLabelSelection(
                        LabelSelection::forLabels([
                            new Label('label1', new FrequencyModifier(1,1)),
                            new Label('label2', new FrequencyModifier(0,1)),
                            new Label('label3', new FrequencyModifier(1,0)),
                        ])
                    )->build()
                ]
            )
        );

        $this->assertEquals($expectedProductionA, iterator_to_array($document->getDeclaration('A')->getProductionSet())[0]);

        $expectedProductionB = [
            new Production(
                new Sequence(
                    [
                        Atom\AtomBuilder::get()->withToken(Token::terminatingSymbol('term1'))->build(),
                    ]
                ),
                new FrequencyModifier(1, 1)
            ),
            new Production(
                new Sequence(
                    [
                        Atom\AtomBuilder::get()->withToken(Token::terminatingSymbol('term2'))->build(),
                    ]
                ),
                new FrequencyModifier(0, 1)
            ),
            new Production(
                new Sequence(
                    [
                        Atom\AtomBuilder::get()->withToken(Token::terminatingSymbol('term3'))->build(),
                    ]
                ),
                new FrequencyModifier(1, 0)
            ),
        ];
        $this->assertEquals($expectedProductionB, $document->getDeclaration('B')->getProductionSet()->getProductions());
    }

    /**
     * @test
     */
    public function it_throws_unexpected_token_exception_when_fails_matching_labels()
    {
        $subject = $this->given_a_parser(
            $this->given_a_source_stream(
                <<< GRAMMAR
            A ::= C.(+-label1 | -label2 | /);
GRAMMAR

            )
        );

        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage(
            "Expected token of type(s) PLUS, MINUS, NON_TERMINATING_SYMBOL, TERMINATING_SYMBOL, <SLASH> found instead (Line 1 offset 44)."
        );

        $subject->parse();
    }


    /**
     * @test
     */
    public function it_throws_no_atom_found_exception()
    {
        $subject = $this->given_a_parser(
            $this->given_a_source_stream(
                <<< GRAMMAR
            A ::= ;
GRAMMAR

            )
        );

        $this->expectException(NoAtomFoundException::class);
        $this->expectExceptionMessage("Expected to match atom sequence, but no atom found. <SEMICOLON> found instead (Line 1 offset 20).");

        $subject->parse();
    }

    /**
     * @test
     */
    public function it_does_not_support_dangling_commas_in_positional_generators()
    {
        $subject = $this->given_a_parser(
            $this->given_a_source_stream(
                <<< GRAMMAR
            A ::= a b,;
GRAMMAR

            )
        );

        $this->expectException(NoAtomFoundException::class);
        $this->expectExceptionMessage("Expected to match atom sequence, but no atom found. <SEMICOLON> found instead (Line 1 offset 24).");

        $subject->parse();
    }
}
