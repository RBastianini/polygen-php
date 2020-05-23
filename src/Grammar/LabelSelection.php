<?php

namespace Polygen\Grammar;

use Polygen\Language\Interpretation\Context;
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
    private $selectedLabels;

    /**
     * @param Label[]|null $selectedLabels
     */
    private function __construct(array $selectedLabels = null)
    {
        // When constructing a new LabelSelection, we do not ensure the labels are unique. This is to respect any
        // eventual label repetition used to influence the probability of a label selection.
        if ($selectedLabels !== null) {
            Assert::allIsInstanceOf($selectedLabels, Label::class);
            $selectedLabels = array_values($selectedLabels);
            sort($selectedLabels);
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

    /**
     * Add a new label to the selection.
     * @return static
     */
    public function add(Label $label)
    {
        Assert::false(
            $this->isLabelResetting(),
            sprintf("Cannot call %s on a label resetting LabelSelection.", __METHOD__)
        );
        return new static(array_merge($this->selectedLabels, [$label]));
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->selectedLabels === [];
    }

    /**
     * @return Label[]
     */
    public function getUniqueLabels()
    {
        if ($this->isLabelResetting()) {
            return [];
        }
        $labels = [];
        foreach ($this->selectedLabels as $label) {
            $labels[$label->getName()] = $label;
        }
        sort($labels);
        return $labels;
    }

    /**
     * @return Label
     */
    public function getRandomLabel(Context $param)
    {
        Assert::false($this->isLabelResetting(), sprintf('Cannot call %s on a label resetting LabelSelection.', __METHOD__));
        Assert::false($this->isEmpty(), sprintf('Cannot call %s on an empty LabelSelection.', __METHOD__));
        return $this->selectedLabels[$param->getRandomNumber(0, count($this->selectedLabels) - 1)];
    }
}
