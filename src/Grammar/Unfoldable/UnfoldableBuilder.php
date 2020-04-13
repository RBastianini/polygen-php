<?php

namespace Polygen\Grammar\Unfoldable;

use Polygen\Grammar\FoldingModifier;
use Polygen\Grammar\LabelSelection;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\SubproductionUnfoldable;
use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;
use Webmozart\Assert\Assert;

/**
 * Builder for unfoldables, so that various parameters can be configured before the actual unfoldable creation,
 * without having to add too many setters to the various unfoldables.
 * It also allows creating an unfoldable from an existing one, by loading in the existing unfoldable configuration.
 * I'm not sure whether I actually needed this or not, but it was late night when I wrote it and it seemed like a good
 * idea at that time.
 */
class UnfoldableBuilder
{
    /**
     * @var Token|null
     */
    private $nonTerminatingToken;

    /**
     * @var SubproductionUnfoldableType|null
     */
    private $type;

    /**
     * @var Subproduction|null
     */
    private $subproduction;

    /**
     * @var FoldingModifier|null
     */
    private $foldingModifier;

    /**
     * @var LabelSelection|null
     */
    private $labelSelection;

    private function __construct(
        Token $nonTerminatingToken = null,
        SubproductionUnfoldableType $unfoldableType = null,
        Subproduction $subproduction = null,
        FoldingModifier $foldingModifier = null,
        LabelSelection $labelSelection = null
    ) {
        $this->nonTerminatingToken = $nonTerminatingToken;
        $this->subproduction = $subproduction;
        $this->type = $unfoldableType;
        $this->foldingModifier = $foldingModifier;
        $this->labelSelection = $labelSelection;
    }

    /**
     * Returns a builder.
     *
     * @return static
     */
    public static function get() {
        return new static();
    }

    /**
     * Returns a builder already preloaded with the passed unfoldable configuration.
     *
     * @return static
     */
    public static function like(AbstractUnfoldable $unfoldable)
    {
        return new static(
            $unfoldable instanceof NonTerminatingSymbol
                ? $unfoldable->getToken()
                : null,
            $unfoldable instanceof SubproductionUnfoldable
                ? $unfoldable->getType()
                : null,
            $unfoldable instanceof SubproductionUnfoldable
                ? $unfoldable->getSubproduction()
                : null,
            $unfoldable->getFoldingModifier(),
            $unfoldable->getLabelSelection()
        );
    }

    /**
     * @return static
     */
    public function withNonTerminatingToken(Token $nonTerminatingToken)
    {
        Assert::eq($nonTerminatingToken->getType(), Type::nonTerminatingSymbol());
        $clone = clone $this;
        $clone->nonTerminatingToken = $nonTerminatingToken;
        return $clone;
    }

    /**
     * @return static
     */
    public function simple()
    {
        $clone = clone $this;
        $clone->type = SubproductionUnfoldableType::simple();
        return $clone;
    }

    /**
     * @return static
     */
    public function iteration()
    {
        $clone = clone $this;
        $clone->type = SubproductionUnfoldableType::iteration();
        return $clone;
    }

    /**
     * @return static
     */
    public function permutation()
    {
        $clone = clone $this;
        $clone->type = SubproductionUnfoldableType::permutation();
        return $clone;
    }

    /**
     * @return static
     */
    public function optional()
    {
        $clone = clone $this;
        $clone->type = SubproductionUnfoldableType::optional();
        return $clone;
    }

    /**
     * @return static
     */
    public function deepUnfold()
    {
        $clone = clone $this;
        $clone->type = SubproductionUnfoldableType::deepUnfold();
        return $clone;
    }

    /**
     * @return self
     */
    public function withLabelSelection(LabelSelection $labelSelection)
    {
        $clone = clone $this;
        $clone->labelSelection = $labelSelection;
        return $clone;
    }

    /**
     * @return static
     */
    public function withFoldingModifier(FoldingModifier $modifier = null)
    {
        $clone = clone $this;
        $clone->foldingModifier = $modifier;
        return $clone;
    }

    /**
     * @return static
     */
    public function withSubproduction(Subproduction $subproduction)
    {
        $clone = clone $this;
        $clone->subproduction = $subproduction;
        return $clone;
    }

    /**
     * @return SubproductionUnfoldable|NonTerminatingSymbol
     */
    public function build()
    {
        Assert::true(
            $this->nonTerminatingToken !== null xor $this->subproduction !== null,
            'Cannot build an unfoldable with both a non terminating token and a subproduction.'
        );
        $labelSelection = $this->labelSelection ?: LabelSelection::none();
        if ($this->nonTerminatingToken) {
            return new NonTerminatingSymbol(
                $this->nonTerminatingToken,
                $labelSelection,
                $this->foldingModifier
            );
        }
        switch ($this->type) {
            case SubproductionUnfoldableType::simple():
            case SubproductionUnfoldableType::deepUnfold():
            case SubproductionUnfoldableType::optional():
            case SubproductionUnfoldableType::iteration():
            case SubproductionUnfoldableType::permutation():
                return new SubproductionUnfoldable(
                    $this->subproduction,
                    $this->type,
                    $labelSelection,
                    $this->foldingModifier
                );
        }
       throw new \LogicException("Unexpected unfoldable type: {$this->type}.");
    }
}
