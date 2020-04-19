<?php

namespace Polygen\Grammar\Atom;

use Polygen\Grammar\Atom;
use Polygen\Grammar\LabelSelection;
use Polygen\Grammar\Unfoldable\Unfoldable;

/**
 *
 */
class UnfoldableAtom extends Atom
{
    /**
     * @var Unfoldable
     */
    private $unfoldable;

    public function __construct(Unfoldable $unfoldable, LabelSelection $labelSelection)
    {
        $this->unfoldable = $unfoldable;
        parent::__construct($labelSelection);
    }

    /**
     * @return Unfoldable
     */
    public function getUnfoldable()
    {
        return $this->unfoldable;
    }
}
