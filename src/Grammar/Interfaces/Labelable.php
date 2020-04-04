<?php

namespace Polygen\Grammar\Interfaces;

use Polygen\Grammar\Label;

/**
 * Interface for objects that can have labels attached to them.
 * @todo Righit now this is the only common interface between Atoms and Unfoldables. If only I could find a better name!
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

    /**
     * @return boolean
     */
    public function hasLabels();

    /**
     * @return Label[]
     */
    public function getLabels();

    /**
     * Returns a copy of the current node, with no labels.
     *
     * @return static
     */
    public function withoutLabels();
}
