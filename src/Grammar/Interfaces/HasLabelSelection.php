<?php

namespace Polygen\Grammar\Interfaces;

/**
 * Interface for objects that can select labels.
 */
interface HasLabelSelection
{
    /**
     * @return \Polygen\Grammar\LabelSelection
     */
    public function getLabelSelection();
}
