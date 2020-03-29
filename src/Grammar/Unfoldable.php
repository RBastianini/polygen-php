<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\Labelable;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Unfoldable\UnfoldableType;
use Polygen\Language\Token\Token;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen unfoldable.
 */
class Unfoldable implements Labelable, Node
{

    /**
     * @var UnfoldableType
     */
    private $type;

    /**
     * @var SubProduction
     */
    private $subProduction;

    /**
     * @var FoldingModifier|null
     */
    private $foldingModifier;

    /**
     * @var Label[]|null
     */
    private $labels = [];

    /**
     * Whe toggled to true, when resolving this atom, all previously selected labels will be unselected.
     *
     * @var bool
     */
    private $resetLabelSelection = false;

    private function __construct(SubProduction $subProduction, UnfoldableType $type)
    {
        $this->subProduction = $subProduction;
        $this->type = $type;
    }

    /**
     * @param SubProduction $subProduction
     * @return static
     */
    public static function iterate(SubProduction $subProduction)
    {
        return new static($subProduction, UnfoldableType::iteration());
    }

    /**
     * @param SubProduction $subProduction
     * @return static
     */
    public static function optional(SubProduction $subProduction)
    {
        return new static($subProduction, UnfoldableType::optional());
    }

    /**
     * @param SubProduction $subProduction
     * @return static
     */
    public static function permutate(SubProduction $subProduction)
    {
        return new static($subProduction, UnfoldableType::permutation());
    }

    /**
     * @param SubProduction $subProduction
     * @return static
     */
    public static function deepUnfold(SubProduction $subProduction)
    {
        return new static($subProduction, UnfoldableType::deepUnfold());
    }

    public static function nonTerminating(Token $nonTerminatingSymbol)
    {
        return new static(
            new SubProduction(
                [],
                [
                    new Production(
                        [],
                        new Sequence(
                            [
                                Atom::simple($nonTerminatingSymbol)
                            ]
                        )
                    )
                ]
            ),
            UnfoldableType::nonTerminating()
        );
    }

    public static function simple(SubProduction $subProduction)
    {
        return new static($subProduction, UnfoldableType::simple());
    }

    /**
     * @return self
     */
    public function withLabel(Label $label)
    {
        return $this->withLabels([$label]);
    }

    /**
     * @param Label[] $labels
     * @return self
     */
    public function withLabels(array $labels)
    {
        Assert::isEmpty($this->labels);
        Assert::allIsInstanceOf($labels, Label::class);
        $this->labels = $labels;
        return $this;
    }

    /**
     * @param FoldingModifier $foldingModifier
     * @return self
     */
    public function withFoldingModifier(FoldingModifier $foldingModifier)
    {
        Assert::null($this->foldingModifier);
        $this->foldingModifier = $foldingModifier;
        return $this;
    }

    /**
     * @return self
     */
    public function withLabelSelectionResetToggle()
    {
        $this->resetLabelSelection = true;
        return $this;
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @return mixed
     */
    public function traverse(AbstractSyntaxWalker $walker)
    {
        return $walker->walkUnfoldable($this);
    }

    /**
     * @return UnfoldableType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return SubProduction
     */
    public function getSubProduction()
    {
        return $this->subProduction;
    }
}
