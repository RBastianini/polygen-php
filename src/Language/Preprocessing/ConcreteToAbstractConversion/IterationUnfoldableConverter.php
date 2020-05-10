<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Atom;
use Polygen\Grammar\Atom\UnfoldableAtom;
use Polygen\Grammar\Definition;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\LabelSelection;
use Polygen\Grammar\Production;
use Polygen\Grammar\ProductionCollection;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\SubproductionUnfoldableType;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\Preprocessing\Services\IdentifierFactory;
use Polygen\Language\Token\Token;
use Polygen\Utils\DeclarationCollection;
use Webmozart\Assert\Assert;

/**
 * This converters translates iteration unfoldables like
 * (Declaration; Productions)+
 * into
 * ( Something ::= (Declaration; Productions) (_ | Something); Something )
 */
class IterationUnfoldableConverter implements ConverterInterface
{

    /**
     * @var IdentifierFactory
     */
    private $identifierFactory;

    public function __construct(IdentifierFactory $identifierFactory)
    {
        $this->identifierFactory = $identifierFactory;
    }

    /**
     * The lowest the number, the highest the priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return 4;
    }

    /**
     * @param UnfoldableAtom $node
     * @return Node
     */
    public function convert(Node $node, DeclarationCollection $_)
    {
        $unfoldable = $node->getUnfoldable();
        Assert::isInstanceOf($unfoldable, SubproductionUnfoldable::class);
        $definitionName = $this->identifierFactory->getId('IterationUnfoldableConverter');

        return Atom\AtomBuilder::get()
            ->withUnfoldable(
                UnfoldableBuilder::get()
                ->simple()
                ->withSubproduction(
                    new Subproduction(
                    [
                        new Definition(
                            $definitionName,
                            new ProductionCollection(
                                [
                                    new Production(
                                        new Sequence([
                                            Atom\AtomBuilder::like($node) // Carry over labels and folding modifiers
                                                ->withUnfoldable(
                                                    UnfoldableBuilder::get()
                                                        ->simple()
                                                        ->withSubproduction($unfoldable->getSubproduction())
                                                        ->withFoldingModifier($unfoldable->getFoldingModifier())
                                                        ->build()
                                                )
                                            ->build(),
                                            Atom\AtomBuilder::get()
                                                ->withUnfoldable(
                                                    UnfoldableBuilder::get()
                                                        ->simple()
                                                        ->withSubproduction(
                                                            new Subproduction(
                                                                [],
                                                                new ProductionCollection(
                                                                    [
                                                                        new Production(
                                                                            new Sequence(
                                                                                [
                                                                                    new Atom\SimpleAtom(Token::underscore(), LabelSelection::none()),
                                                                                ]
                                                                            )
                                                                        ),
                                                                        new Production(
                                                                            new Sequence(
                                                                                [
                                                                                    Atom\AtomBuilder::get()
                                                                                        ->withUnfoldable(
                                                                                            UnfoldableBuilder::get()
                                                                                                ->withNonTerminatingToken(
                                                                                                    Token::nonTerminatingSymbol($definitionName)
                                                                                                )
                                                                                            ->build()
                                                                                        )
                                                                                    ->build()
                                                                                ]
                                                                            )
                                                                        )
                                                                    ]
                                                                )
                                                            )
                                                        )
                                                    ->build()
                                                )
                                            ->build()
                                        ])
                                    )
                                ]
                            )
                        )
                    ],
                    new ProductionCollection(
                        [
                            new Production(
                                new Sequence(
                                    [
                                        Atom\AtomBuilder::get()
                                            ->withUnfoldable(
                                                UnfoldableBuilder::get()
                                                    ->withNonTerminatingToken(Token::nonTerminatingSymbol($definitionName))
                                                    ->build()
                                            )
                                        ->build()
                                    ]
                                )
                            )
                        ]
                    )
                )
            )->build()
        )->build();
    }

    /**
     * @param Node $node
     * @return bool
     */
    public function canConvert(Node $node)
    {
        return $node instanceof UnfoldableAtom
            && $node->getUnfoldable() instanceof SubproductionUnfoldable
            && $node->getUnfoldable()->getType() === SubproductionUnfoldableType::iteration();
    }
}
