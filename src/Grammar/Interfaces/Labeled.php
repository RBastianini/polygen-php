<?php

namespace Polygen\Grammar\Interfaces;

use Polygen\Grammar\Label;

/**
 * Interface for objects that have a label.
 */
interface Labeled
{
    /**
     * @return Label[]
     */
    public function getSelectedLabels();

    /**
     * @return bool
     */
    public function isResetLabel();
}
