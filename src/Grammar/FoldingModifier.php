<?php

namespace Polygen\Grammar;

use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;
use Polygen\Utils\Enum;

/**
 * @method static FoldingModifier fold()
 * @method static FoldingModifier unfold()
 */
class FoldingModifier extends Enum
{
    const FOLD = 'FOLDING';
    const UNFOLD = 'UNFOLDING';

    public static function fromToken(Token $token)
    {
        switch ($token->getType()) {
            case Type::unfolding():
                return self::unfold();
            case Type::folding():
                return self::fold();
            default:
                throw new \InvalidArgumentException("Cannot create folding modifier from token: $token");
        }
    }
}
