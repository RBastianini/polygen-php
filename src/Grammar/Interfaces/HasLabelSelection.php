<?php

namespace Polygen\Grammar\Interfaces;

use Polygen\Grammar\LabelSelection;

/**
 * Interface for objects that can select labels.
 */
interface HasLabelSelection
{
    /**
     * Returns a copy of this object but with the specified label selection.
     *
     * @return static
     */
    public function withLabelSelection(LabelSelection $labelSelection);

    /**
     * @return \Polygen\Grammar\LabelSelection
     */
    public function getLabelSelection();
}
