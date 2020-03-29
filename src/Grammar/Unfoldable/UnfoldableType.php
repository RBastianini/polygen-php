<?php

namespace Polygen\Grammar\Unfoldable;

use Polygen\Utils\Enum;

/**
 * Enum to represent the types of Unfoldable.
 *
 * @method static UnfoldableType permutation()
 * @method static UnfoldableType deepUnfold()
 * @method static UnfoldableType optional()
 * @method static UnfoldableType iteration()
 * @method static UnfoldableType nonTerminating()
 * @method static UnfoldableType simple()
 */
class UnfoldableType extends Enum
{
    const PERMUTATION = 'PERMUTATION';
    const DEEP_UNFOLD = 'DEEP_UNFOLD';
    const OPTIONAL = 'OPTIONAL';
    const ITERATION = 'ITERATION';
    const NON_TERMINATING = 'NON_TERMINATING';
    const SIMPLE = 'SIMPLE';
}
