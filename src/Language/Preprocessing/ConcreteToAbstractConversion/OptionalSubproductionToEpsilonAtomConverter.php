<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Atom;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\LabelSelection;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Grammar\Unfoldable\SubproductionUnfoldableType;
use Polygen\Language\Context;
use Polygen\Language\Token\Token;

/**
 * The idea is to convert optional subproductions
 * [Declarations; Production]
 * to
 * (_ | (Declarations; Production))
 */
class OptionalSubproductionToEpsilonAtomConverter implements ConverterInterface
{

    /**
     * The lowest the number, the highest the priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return 3;
    }

    /**
     * @param SubproductionUnfoldable $node
     * @return Node
     */
    public function convert(Node $node, Context $_)
    {
        return UnfoldableBuilder::get()
            ->simple()
            ->withSubproduction(
            new Subproduction(
                [],
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
                                            ->simple()
                                            ->withSubproduction($node->getSubproduction())
                                            ->build()
                                    )
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
            && $node->getType() === SubproductionUnfoldableType::optional();
    }
}
