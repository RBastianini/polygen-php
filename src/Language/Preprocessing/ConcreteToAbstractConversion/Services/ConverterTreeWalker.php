<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services;

use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom;
use Polygen\Grammar\Atom\UnfoldableAtom;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Definition;
use Polygen\Grammar\Interfaces\HasDeclarations;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\ProductionCollection;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\NonTerminatingSymbol;
use Polygen\Grammar\Unfoldable\SubproductionUnfoldableType;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Document;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\ConverterInterface;
use Polygen\Utils\DeclarationCollection;

/**
 * This object takes a converter upon construction and can be invoked through the "convert" method, passing a Document in.
 * It will then walk the entire document, applying the converter to all nodes, returning the converted document.
 */
class ConverterTreeWalker implements AbstractSyntaxWalker
{
    /**
     * @var \Polygen\Language\Preprocessing\ConcreteToAbstractConversion\ConverterInterface
     */
    private $converter;

    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @param Document $document
     * @return Document
     */
    public function convert(Document $document)
    {
        return $this->convertOne($document, new DeclarationCollection());
    }

    /**
     * @param DeclarationCollection $context
     * @return Document
     *@internal
     */
    public function walkDocument(Document $document, $context = null)
    {
        return new Document(
            $this->convertAll($document->getDeclarations(), $context)
        );
    }

    /**
     * @param DeclarationCollection $context
     * @return Definition
     *@internal
     */
    public function walkDefinition(Definition $definition, $context = null)
    {
        return new Definition(
            $definition->getName(),
            new ProductionCollection(
                $this->convertAll(
                    $definition->getProductionSet()
                        ->getProductions(),
                    $context
                )
            )
        );
    }

    /**
     * @param DeclarationCollection $context
     * @return Assignment
     *@internal
     */
    public function walkAssignment(Assignment $assignment, $context = null)
    {
        return new Assignment(
            $assignment->getName(),
            new ProductionCollection(
                $this->convertAll(
                    $assignment->getProductionSet()
                        ->getProductions(),
                    $context
                )
            )
        );
    }

    /**
     * @param DeclarationCollection $context
     * @return Sequence
     *@internal
     */
    public function walkSequence(Sequence $sequence, $context = null)
    {
        return new Sequence(
            $this->convertAll($sequence->getSequenceContents(), $context),
            $sequence->getLabel()
        );
    }

    /**
     * @param DeclarationCollection $context
     * @return Production
     *@internal
     */
    public function walkProduction(Production $production, $context = null)
    {
        return new Production(
            $this->convertOne($production->getSequence(), $context),
            $production->getFrequencyModifier()
        );
    }

    /**
     * @param DeclarationCollection $context
     * @return Subproduction
     *@internal
     */
    public function walkSubproduction(Subproduction $subproduction, $context = null)
    {
        return new Subproduction(
            $this->convertAll($subproduction->getDeclarations(), $context),
            new ProductionCollection($this->convertAll($subproduction->getProductionSet()->getProductions(), $context))
        );
    }

    /**
     * @param DeclarationCollection $context
     * @return Atom\SimpleAtom
     *@internal
     */
    public function walkSimpleAtom(Atom\SimpleAtom $atom, $context = null)
    {
        return $atom;
    }

    /**
     * @param DeclarationCollection $context
     * @return mixed|\Polygen\Grammar\AtomSequence
     *@internal
     */
    public function walkAtomSequence(AtomSequence $atomSequence, $context = null)
    {
        return new AtomSequence(
            $this->convertAll($atomSequence->getAtoms(), $context)
        );
    }

    /**
     * @param DeclarationCollection $context
     * @return \Polygen\Grammar\SubproductionUnfoldable
     *@internal
     */
    public function walkSubproductionUnfoldable(SubproductionUnfoldable $unfoldable, $context = null)
    {
        switch ($unfoldable->getType()) {
            case SubproductionUnfoldableType::simple():
                return UnfoldableBuilder::like($unfoldable)->withSubproduction(
                    $this->convertOne($unfoldable->getSubproduction(), $context)
                )->build();
            case SubproductionUnfoldableType::optional():
                return UnfoldableBuilder::like($unfoldable)->withSubproduction(
                    $this->convertOne($unfoldable->getSubproduction(), $context)
                )->build();
            case SubproductionUnfoldableType::permutation():
            case SubproductionUnfoldableType::deepUnfold():
            case SubproductionUnfoldableType::iteration():
                return UnfoldableBuilder::like($unfoldable)
                    ->withSubproduction(
                        $this->convertOne($unfoldable->getSubproduction(), $context)
                    )
                    ->build();
            default:
                throw new \LogicException('Well how did you get here in the first place?');
        }
    }

    /**
     * @param DeclarationCollection $context
     * @return NonTerminatingSymbol
     *@internal
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol, $context = null)
    {
        return $nonTerminatingSymbol;
    }

    /**
     * @param DeclarationCollection $context
     * @return UnfoldableAtom
     *@internal
     */
    public function walkUnfoldableAtom(Atom\UnfoldableAtom $atom, $context = null)
    {
        return Atom\AtomBuilder::like($atom)->withUnfoldable(
            $this->convertOne($atom->getUnfoldable(), $context)
        )->build();
    }

    /**
     * @internal
     * @param Node[] $nodes
     * @return Node[]
     */
    private function convertAll(array $nodes, DeclarationCollection $context)
    {
        return array_map(
            [$this, 'convertOne'],
            $nodes,
            array_fill(0, count($nodes), $context)
        );
    }

    /**
     * @return Node
     */
    private function convertOne(Node $node, DeclarationCollection $context)
    {
        if ($node instanceof HasDeclarations) {
            $context = $context->mergeDeclarations($node->getDeclarations());
        }
        $node = $this->converter->canConvert($node)
            ? $this->converter->convert($node, $context)
            : $node;
        return $node->traverse($this, $context);
    }
}
