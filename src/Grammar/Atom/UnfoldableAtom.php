<?php

namespace Polygen\Grammar\Atom;

use Polygen\Grammar\Atom;
use Polygen\Grammar\LabelSelection;
use Polygen\Grammar\Unfoldable\Unfoldable;
use Polygen\Language\AbstractSyntaxWalker;

/**
 * An atom containing an unfoldable.
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
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkUnfoldableAtom($this, $context);
    }

    /**
     * @return Unfoldable
     */
    public function getUnfoldable()
    {
        return $this->unfoldable;
    }
}
