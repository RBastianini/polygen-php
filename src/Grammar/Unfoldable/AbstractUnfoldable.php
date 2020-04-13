<?php

namespace Polygen\Grammar\Unfoldable;

use Polygen\Grammar\FoldingModifier;
use Polygen\Grammar\Interfaces\HasLabelSelection;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\LabelSelection;

/**
 * Unfoldable abstract parent class.
 */
abstract class AbstractUnfoldable implements Node, HasLabelSelection
{
    /**
     * @var LabelSelection
     */
    private $labelSelection;

    /**
     * @var FoldingModifier|null
     */
    private $foldingModifier;

    public function __construct(
        LabelSelection $labelSelection,
        FoldingModifier $foldingModifier = null
    ) {
        $this->labelSelection = $labelSelection;
        $this->foldingModifier = $foldingModifier;
    }

    /**
     * @return LabelSelection
     */
    public function getLabelSelection()
    {
       return $this->labelSelection;
    }

    /**
     * @return static
     */
    public function withLabelSelection(LabelSelection $labelSelection)
    {
        return UnfoldableBuilder::like($this)->withLabelSelection($labelSelection)->build();
    }

    /**
     * @return FoldingModifier|null
     */
    public function getFoldingModifier()
    {
        return $this->foldingModifier;
    }
}
