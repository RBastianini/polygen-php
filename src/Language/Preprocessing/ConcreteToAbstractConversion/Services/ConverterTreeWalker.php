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
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\NonTerminatingSymbol;
use Polygen\Grammar\Unfoldable\SubproductionUnfoldableType;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Context;
use Polygen\Language\Document;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\ConverterInterface;

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
        return $this->convertOne($document, new Context());
    }

    /**
     * @internal
     * @param Context $context
     * @return Document
     */
    public function walkDocument(Document $document, $context = null)
    {
        return new Document(
            $this->convertAll($document->getDeclarations(), $context)
        );
    }

    /**
     * @internal
     * @param Context $context
     * @return Definition
     */
    public function walkDefinition(Definition $definition, $context = null)
    {
        return new Definition(
            $definition->getName(),
            $this->convertAll($definition->getProductions(), $context)
        );
    }

    /**
     * @internal
     * @param Context $context
     * @return Assignment
     */
    public function walkAssignment(Assignment $assignment, $context = null)
    {
        return new Assignment(
            $assignment->getName(),
            $this->convertAll($assignment->getProductions(), $context)
        );
    }

    /**
     * @internal
     * @param Context $context
     * @return Sequence
     */
    public function walkSequence(Sequence $sequence, $context = null)
    {
        return new Sequence(
            $this->convertAll($sequence->getSequenceContents(), $context),
            $sequence->getLabel()
        );
    }

    /**
     * @internal
     * @param Context $context
     * @return Production
     */
    public function walkProduction(Production $production, $context = null)
    {
        return new Production(
            $this->convertOne($production->getSequence(), $context),
            $production->getFrequencyModifier()
        );
    }

    /**
     * @internal
     * @param Context $context
     * @return Subproduction
     */
    public function walkSubproduction(Subproduction $subproduction, $context = null)
    {
        return new Subproduction(
            $this->convertAll($subproduction->getDeclarations(), $context),
            $this->convertAll($subproduction->getProductions(), $context)
        );
    }

    /**
     * @internal
     * @param Context $context
     * @return Atom\SimpleAtom
     */
    public function walkSimpleAtom(Atom\SimpleAtom $atom, $context = null)
    {
        return $atom;
    }

    /**
     * @internal
     * @param Context $context
     * @return mixed|\Polygen\Grammar\AtomSequence
     */
    public function walkAtomSequence(AtomSequence $atomSequence, $context = null)
    {
        return new AtomSequence(
            $this->convertAll($atomSequence->getAtoms(), $context)
        );
    }

    /**
     * @internal
     * @param Context $context
     * @return \Polygen\Grammar\SubproductionUnfoldable
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
     * @internal
     * @param Context $context
     * @return NonTerminatingSymbol
     */
    public function walkNonTerminating(NonTerminatingSymbol $nonTerminatingSymbol, $context = null)
    {
        return $nonTerminatingSymbol;
    }

    /**
     * @internal
     * @param Context $context
     * @return UnfoldableAtom
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
    private function convertAll(array $nodes, Context $context)
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
    private function convertOne(Node $node, Context $context)
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
