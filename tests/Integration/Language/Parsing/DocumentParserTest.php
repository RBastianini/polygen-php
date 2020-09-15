<?php

namespace Tests\Integration\Language\Parsing;

use Polygen\Grammar\Atom;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\FrequencyModifier;
use Polygen\Grammar\Label;
use Polygen\Grammar\LabelSelection;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\Document;
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
            $document->getDeclaration('A')->getProductions(),
            $document->getDeclaration('B')->getProductions()
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

        $this->assertEquals($expectedProductionA, $document->getDeclaration('A')->getProductions()[0]);

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
        $this->assertEquals($expectedProductionB, $document->getDeclaration('B')->getProductions());
    }

    // TODO: for every new parsing bug, remember to add a dedicated test.
}
