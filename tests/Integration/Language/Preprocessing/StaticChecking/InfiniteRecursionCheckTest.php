<?php

namespace Tests\Integration\Language\Preprocessing\StaticChecking;

use Mockery;
use Mockery\MockInterface;
use Polygen\Language\Errors\InfiniteRecursion;
use Polygen\Language\Preprocessing\Services\IdentifierFactory;
use Polygen\Language\Preprocessing\StaticCheck\InfiniteRecursionCheck;
use Tests\DocumentUtils;
use Tests\StreamUtils;
use Tests\TestCase;

/**
 *
 */
class InfiniteRecursionCheckTest extends TestCase
{
    use DocumentUtils;
    use StaticCheckUtils;
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
    public function it_detects_top_level_loops()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    S ::= a | b | c D | E |(E ::= D; E | a | D);
                    D ::= E | b | c;
                    E ::= E;                        
GRAMMAR
            )
        );

        $this->identifierFactory->shouldReceive('getId')
            ->once()
            ->with('context')
            ->andReturn($innerContextName = 'inner-context');

        $staticChecker = $this->given_a_static_checker_with(new InfiniteRecursionCheck($this->identifierFactory));

        $errorCollection = $staticChecker->check($document);

        $expectedErrors = [new InfiniteRecursion($document->getDeclaration('E'))];

        $this->assertEquals($expectedErrors, $errorCollection->getErrors());
    }

    /**
     * @test
     */
    public function it_detects_nested_level_loops()
    {
        $document = $this->given_a_document(
            $this->given_a_source_stream(
                <<<GRAMMAR
                    S ::= a | b | c D | E |(E ::= E; E | a | D);
                    D ::= E;
                    E ::= c;                        
GRAMMAR
            )
        );

        $staticChecker = $this->given_a_static_checker_with(
            new InfiniteRecursionCheck($this->identifierFactory)
        );

        $this->identifierFactory->shouldReceive('getId')
            ->once()
            ->with('context')
            ->andReturn($innerContextName = 'inner-context');

        $errorCollection = $staticChecker->check($document);

        // It takes some effort to extract the inner definition of E for the error message.
        /** @var \Polygen\Grammar\SubproductionUnfoldable $unfoldable */
        $unfoldable = $document->getDeclaration('S')
            ->getProductions()[4]
            ->getSequence()
            ->getSequenceContents()[0]
            ->getUnfoldable();

        $expectedErrors = [
            new InfiniteRecursion(
                $unfoldable->getSubproduction()
                    ->getDeclarations()[0]
            )
        ];

        $this->assertEquals($expectedErrors, $errorCollection->getErrors());
    }

    /**
     * @test
     */
    public function it_detects_no_loops_when_there_isnt_any()
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

        $staticChecker = $this->given_a_static_checker_with(
            new InfiniteRecursionCheck($this->identifierFactory)
        );

        $this->identifierFactory->shouldReceive('getId')
            ->once()
            ->with('context')
            ->andReturn($innerContextName = 'inner-context');

        $errorCollection = $staticChecker->check($document);

        $this->assertEmpty($errorCollection->getErrors());
    }
}
