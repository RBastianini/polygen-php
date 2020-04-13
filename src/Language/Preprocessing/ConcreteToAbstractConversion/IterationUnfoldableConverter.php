<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Atom;
use Polygen\Grammar\Definition;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\SubproductionUnfoldableType;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\IdentifierFactory;
use Polygen\Language\Token\Token;
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
     * @param SubproductionUnfoldable $node
     * @return Node
     */
    public function convert(Node $node)
    {
        Assert::isInstanceOf($node, SubproductionUnfoldable::class);
        $definitionName = $this->identifierFactory->getId('IterationUnfoldableConverter');

        return UnfoldableBuilder::get()
            ->simple()
            ->withSubproduction(
                new Subproduction(
                [
                    new Definition(
                        $definitionName,
                        [
                            new Production(
                                new Sequence([
                                    UnfoldableBuilder::get()
                                        ->simple()
                                        ->withSubproduction($node->getSubproduction())
                                        ->withFoldingModifier($node->getFoldingModifier())
                                        ->withLabelSelection($node->getLabelSelection())
                                        ->build(),
                                    UnfoldableBuilder::get()
                                    ->simple()
                                    ->withSubproduction(
                                        new Subproduction(
                                            [],
                                            [
                                                new Production(
                                                    new Sequence(
                                                        [
                                                            Atom::simple(Token::underscore()),
                                                        ]
                                                    )
                                                ),
                                                new Production(
                                                    new Sequence(
                                                        [
                                                            UnfoldableBuilder::get()
                                                                ->withNonTerminatingToken(
                                                                    Token::nonTerminatingSymbol($definitionName)
                                                                )->build()
                                                        ]
                                                    )
                                                )
                                            ]
                                        )
                                    )->build()
                                ])
                            )
                        ]
                    )
                ],
                    [
                        new Production(
                            new Sequence(
                                [
                                    UnfoldableBuilder::get()
                                        ->withNonTerminatingToken(Token::nonTerminatingSymbol($definitionName))
                                        ->build()
                                ]
                            )
                        )
                    ]
            )
        )->build();
    }

    /**
     * @param Node $node
     * @return bool
     */
    public function canConvert(Node $node)
    {
        return $node instanceof SubproductionUnfoldable
            && $node->getType() === SubproductionUnfoldableType::iteration();
    }
}
