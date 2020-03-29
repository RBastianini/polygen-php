<?php

namespace Polygen\Grammar\Interfaces;

use Polygen\Grammar\Label;

/**
 * Interface for objects that can have labels attached to them.
 */
interface Labelable
{
    /**
     * @return self
     */
    public function withLabel(Label $label);

    /**
     * @param Label[] $labels
     * @return self
     */
    public function withLabels(array $labels);

    /**
     * Enables the label selection reset.
     *
     * @return self
     */
    public function withLabelSelectionResetToggle();
}
