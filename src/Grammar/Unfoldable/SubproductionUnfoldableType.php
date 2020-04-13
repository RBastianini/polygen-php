<?php

namespace Polygen\Grammar\Unfoldable;

use Polygen\Utils\Enum;

/**
 * Enum to represent the types of Unfoldable.
 *
 * @method static SubproductionUnfoldableType permutation()
 * @method static SubproductionUnfoldableType deepUnfold()
 * @method static SubproductionUnfoldableType optional()
 * @method static SubproductionUnfoldableType iteration()
 * @method static SubproductionUnfoldableType simple()
 */
class SubproductionUnfoldableType extends Enum
{
    const PERMUTATION = 'PERMUTATION';
    const DEEP_UNFOLD = 'DEEP_UNFOLD';
    const OPTIONAL = 'OPTIONAL';
    const ITERATION = 'ITERATION';
    const SIMPLE = 'SIMPLE';
}
