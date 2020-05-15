<?php

namespace Tests\Integration\Language\Preprocessing\StaticChecking\InfiniteRecursionCheck;

use Mockery;
use Mockery\MockInterface;
use Polygen\Language\Preprocessing\Services\IdentifierFactory;
use Polygen\Language\Preprocessing\StaticCheck\InfiniteRecursionCheck\ReferenceGraph;
use Polygen\Language\Preprocessing\StaticCheck\InfiniteRecursionCheck\ReferenceGraphBuilder;
use Tests\DocumentUtils;
use Tests\StreamUtils;
use Tests\TestCase;

class ReferenceGraphBuilderTest extends TestCase
{
    use DocumentUtils;
    use StreamUtils;

    /**
     * @var MockInterface|IdentifierFactory
     */
    private $identifierFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->identifierFactory = Mockery::mock(IdentifierFactory::class);
    }

    /**
     * @test
     */
    public function it_builds_a_simple_reference_graph()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    S ::= a | b | c D (E | _);
                    D ::= E | b | c;
                    E ::= E;                        
GRAMMAR
            )
        );
        $subject = new ReferenceGraphBuilder($this->identifierFactory);
        $result = $subject->build($document);

        $expectedReferences = [
            'root>S' => [
                'root>D' => 'root>D',
                'root>E' => 'root>E',
                ReferenceGraph::TERMINATING_SYMBOL => ReferenceGraph::TERMINATING_SYMBOL
            ],
            'root>D' => [
                'root>E' => 'root>E',
                ReferenceGraph::TERMINATING_SYMBOL => ReferenceGraph::TERMINATING_SYMBOL
            ],
            'root>E' => [
                'root>E' => 'root>E',
            ],
        ];

        $this->assertEquals($expectedReferences, $result->getReferencedDeclarationsByUniqueName());

        $expectedDeclarationsByUniqueName = [
            'root>S' => $document->getDeclaration('S'),
            'root>D' => $document->getDeclaration('D'),
            'root>E' => $document->getDeclaration('E'),
        ];
        $this->assertEquals($expectedDeclarationsByUniqueName, $result->getDeclarationsByUniqueName());
    }


    /**
     * @test
     */
    public function it_builds_a_more_complex_reference_graph()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    S ::= a | b | c D | E |(E ::= D; E | a | D);
                    D ::= E | b | c;
                    E ::= E | D;                        
GRAMMAR
            )
        );

        $this->identifierFactory->shouldReceive('getId')
            ->once()
            ->with('context')
            ->andReturn($innerContextName = 'inner-context');

        $subject = new ReferenceGraphBuilder($this->identifierFactory);
        $result = $subject->build($document);

        $expectedReferences = [
            'root>S' => [
                'root>D' => 'root>D',
                'root>E' => 'root>E',
                // S must not reference the outer-most definition of E, but the inner one.
                "$innerContextName>E" => "$innerContextName>E",
                ReferenceGraph::TERMINATING_SYMBOL => ReferenceGraph::TERMINATING_SYMBOL,
            ],
            'root>D' => [
                'root>E' => 'root>E',
                ReferenceGraph::TERMINATING_SYMBOL => ReferenceGraph::TERMINATING_SYMBOL,
            ],
            'root>E' => [
                'root>E' => 'root>E',
                // Conversely, E must reference the outer-most definition of D, since it has nothing to do with the
                // inner scope where D is redefined.
                'root>D' => 'root>D',
            ],
            "$innerContextName>E" => [
                'root>D' => 'root>D',
            ]
        ];

        $this->assertEquals($expectedReferences, $result->getReferencedDeclarationsByUniqueName());

        $expectedDeclarationsByUniqueName = [
            'root>S' => $document->getDeclaration('S'),
            'root>D' => $document->getDeclaration('D'),
            'root>E' => $document->getDeclaration('E'),
            // The nested E declaration takes some effort to be accessed from the document root.
            "$innerContextName>E" => $document->getDeclaration('S')
                ->getProductions()[4]
                ->getSequence()
                ->getSequenceContents()[0]
                ->getUnfoldable()->getSubproduction()
                ->getDeclarations()[0],
        ];
        $this->assertEquals($expectedDeclarationsByUniqueName, $result->getDeclarationsByUniqueName());
    }
}
