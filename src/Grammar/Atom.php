<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\HasLabelSelection;
use Polygen\Grammar\Interfaces\Node;

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
     * @return \Polygen\Grammar\LabelSelection
     */
    public function getLabelSelection()
    {
        return $this->labelSelection;
    }
}
