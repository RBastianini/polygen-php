<?php

namespace Tests\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\MultipleLabelSelectionGroupConverter;
use Tests\DocumentUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class MultipleLabelSelectionGroupConverterTest extends TestCase
{
    use ConverterUtils;
    use DocumentUtils;
    use StreamUtils;

    /**
     * @var MultipleLabelSelectionGroupConverter
     */
    private $subject;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->given_a_converter_with(
            new MultipleLabelSelectionGroupConverter()
        );
    }

    /**
     * @test
     */
    public function it_ignores_atoms_with_no_selection_group()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    Def ::= term Nonterm (Def ::= asd; prod);
GRAMMAR
            )
        );

        $converted = $this->subject->convert($document);

        $this->assertEquals($document->getDeclaration('Def'), $converted->getDeclaration('Def'));
    }

    /**
     * @test
     */
    public function it_ignores_atoms_with_just_one_selection_group()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    Def ::= term.a Nonterm. (Def ::= asd; prod).(a|b);
GRAMMAR
            )
        );

        $converted = $this->subject->convert($document);

        $this->assertEquals($document->getDeclaration('Def'), $converted->getDeclaration('Def'));
    }

    /**
     * @test
     */
    public function it_converts_non_terminating_symbols_with_more_than_one_selection_group()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    Original ::= term1 Nonterm1 term2.a.b..c.d term3 Nonterm2;
                    Converted ::= term1 Nonterm1 ((((term2.a).b).).c).d term3 Nonterm2;
GRAMMAR
            )
        );

        $converted = $this->subject->convert($document);

        $this->assertEquals(
            $converted->getDeclaration('Converted')->getProductionSet(),
            $converted->getDeclaration('Original')->getProductionSet()
        );
    }

    /**
     * @test
     */
    public function it_converts_subproductions_with_more_than_one_selection_group_2()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    Original ::= term1 Nonterm1 (Def ::= term3; term4 | Nonterm2).a.b..c.d term5 Nonter3;
                    Converted ::= term1 Nonterm1 (((((Def ::= term3; term4 | Nonterm2).a).b).).c).d term5 Nonter3;
GRAMMAR
            )
        );

        $converted = $this->subject->convert($document);

        $this->assertEquals(
            $converted->getDeclaration('Converted')->getProductionSet(),
            $converted->getDeclaration('Original')->getProductionSet()
        );
    }

    /**
     * @test
     */
    public function it_converts_terminating_symbols_with_more_than_one_selection_group()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    Original ::= term1 Nonterm1 term2.a.b..c.d term3 Nonterm2;
                    Converted ::= term1 Nonterm1 ((((term2.a).b).).c).d term3 Nonterm2;
GRAMMAR
            )
        );

        $converted = $this->subject->convert($document);

        $this->assertEquals(
            $converted->getDeclaration('Converted')->getProductionSet(),
            $converted->getDeclaration('Original')->getProductionSet()
        );
    }
}
