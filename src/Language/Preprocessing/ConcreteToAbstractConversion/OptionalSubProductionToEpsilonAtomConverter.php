<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Atom;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\SubProduction;
use Polygen\Grammar\Unfoldable;
use Polygen\Grammar\Unfoldable\UnfoldableType;
use Polygen\Language\Token\Token;

/**
 * The idea is to convert optional subproductions
 * [Declarations; Production]
 * to
 * (_ | (Declarations; Production))
 */
class OptionalSubProductionToEpsilonAtomConverter implements ConverterInterface
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
     * @param Unfoldable $node
     * @return Node
     */
    public function convert(Node $node)
    {
        return Unfoldable::simple(
            new SubProduction(
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
                                Unfoldable::simple(
                                    $node->getSubProduction()
                                )
                            ]
                        )
                    )
                ]
            )
        );
    }

    /**
     * @param Node $node
     * @return bool
     */
    public function canConvert(Node $node)
    {
        return $node instanceof Unfoldable
            && $node->getType() === UnfoldableType::optional();
    }
}
