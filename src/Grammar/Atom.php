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
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkAtom($this, $context);
    }

    /**
     * @return \Polygen\Grammar\LabelSelection
     */
    public function getLabelSelection()
    {
        return $this->labelSelection;
    }
}
