<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\HasLabelSelection;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;

/**
 * Atom Polygen node.
 */
abstract class Atom implements HasLabelSelection, Node
{
    /**
     * @var LabelSelection
     */
    private $labelSelection;

    /**
     * Atom constructor.
     *
     * @param Label[] $labels
     * @param null $foldingModifier
     */
    protected function __construct(LabelSelection $labelSelection)
    {
        $this->labelSelection = $labelSelection;
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @return mixed
     */
    public function traverse(AbstractSyntaxWalker $walker)
    {
        return $walker->walkAtom($this);
    }

    /**
     * @return \Polygen\Grammar\LabelSelection
     */
    public function getLabelSelection()
    {
        return $this->labelSelection;
    }
}
