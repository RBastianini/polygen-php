<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\Node;
use Polygen\Utils\LabelSelectionCollection;

/**
 * Atom Polygen node.
 */
abstract class Atom implements Node
{
    /**
     * @var LabelSelection
     */
    private $labelSelections;

    /**
     * Atom constructor.
     *
     * @param Label[] $labels
     * @param null $foldingModifier
     */
    protected function __construct(LabelSelectionCollection $labelSelection)
    {
        $this->labelSelections = $labelSelection;
    }

    /**
     * @return LabelSelection
     */
    public function getLabelSelection()
    {
        return $this->labelSelections;
    }

    /**
     * @return LabelSelectionCollection
     */
    public function getLabelSelections()
    {
        return $this->labelSelections;
    }
}
