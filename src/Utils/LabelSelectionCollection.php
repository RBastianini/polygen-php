<?php

namespace Polygen\Utils;

use Polygen\Grammar\Label;
use Polygen\Grammar\LabelSelection;
use Webmozart\Assert\Assert;

/**
 * Utility class to hold label selections.
 * This is used to support the
 * Unfoldable.(Label11|Label12|...|Label1n).(Label21|Label22|...|Label2j) syntax that, although not present in the
 * formal language definition, is apparently supported by Polygen.
 */
class LabelSelectionCollection
{

    /**
     * @var LabelSelection[]
     */
    private $labelSelections;

    /**
     * LabelSelectionCollection constructor.
     *
     * @param LabelSelection[] $labelSelections
     */
    public function __construct(array $labelSelections = [])
    {
        Assert::allIsInstanceOf($labelSelections, LabelSelection::class);
        $this->labelSelections = array_values($labelSelections);
    }

    /**
     * @return LabelSelection[]
     */
    public function getLabelSelections()
    {
        return $this->labelSelections;
    }

    /**
     * @return Label[]
     */
    public function getAllLabels()
    {
        $result = [];
        foreach ($this->labelSelections as $labelSelection) {
            $result = array_merge($result, $labelSelection->getLabels());
        }
        return $result;
    }

    /**
     * @return LabelSelection
     */
    public function first()
    {
        Assert::false(
            $this->isEmpty(),
            sprintf("Attempted to call %s on an empty LabelSelectionCollection", __METHOD__)
        );
        return reset($this->labelSelections);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->labelSelections);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->labelSelections);
    }
}
