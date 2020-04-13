<?php

namespace Polygen\Grammar;

use Webmozart\Assert\Assert;

/**
 * Represent a label selection attached to an Atom or Unfoldable.
 */
class LabelSelection
{
    /**
     * It's either a Label array or a null value. If it's null, it means that the intention of this LabelSelection is to
     * reset the current label selection context (useful during production),
     *
     * @var Label[]|null
     */
    private $selectedLabels = [];

    /**
     * @param Label[]|null $selectedLabels
     */
    private function __construct(array $selectedLabels = null)
    {
        if ($selectedLabels !== null) {
            Assert::allIsInstanceOf($selectedLabels, Label::class);
        }
        $this->selectedLabels = $selectedLabels;
    }

    /**
     * @return static
     */
    public static function forLabel(Label $label)
    {
        return new static([$label]);
    }

    /**
     * @param Label[] $selectedLabels
     * @return static
     */
    public static function forLabels(array $selectedLabels)
    {
        return new static($selectedLabels);
    }

    /**
     * Returns a "reset" LabelSelection.
     *
     * @return static
     */
    public static function reset()
    {
        return new static();
    }

    /**
     * An empty label selection.
     *
     * @return static
     */
    public static function none()
    {
        return new static([]);
    }

    /**
     * @return Label[]
     */
    public function getLabels()
    {
        return $this->selectedLabels ? : [];
    }

    /**
     * @return bool
     */
    public function isLabelResetting()
    {
        return $this->selectedLabels === null;
    }
}
