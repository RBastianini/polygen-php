<?php

namespace Polygen\Grammar\Unfoldable;

use Polygen\Grammar\FoldingModifier;
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

    private function __construct(
        Token $nonTerminatingToken = null,
        SubproductionUnfoldableType $unfoldableType = null,
        Subproduction $subproduction = null,
        FoldingModifier $foldingModifier = null
    ) {
        $this->nonTerminatingToken = $nonTerminatingToken;
        $this->subproduction = $subproduction;
        $this->type = $unfoldableType;
        $this->foldingModifier = $foldingModifier;
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
    public static function like(Unfoldable $unfoldable)
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
            $unfoldable->getFoldingModifier()
        );
    }

    /**
     * @return $this
     */
    public function withNonTerminatingToken(Token $nonTerminatingToken)
    {
        Assert::eq($nonTerminatingToken->getType(), Type::nonTerminatingSymbol());
        $this->nonTerminatingToken = $nonTerminatingToken;
        return $this;
    }

    /**
     * @return $this
     */
    public function simple()
    {
        $this->type = SubproductionUnfoldableType::simple();
        return $this;
    }

    /**
     * @return $this
     */
    public function iteration()
    {
        $this->type = SubproductionUnfoldableType::iteration();
        return $this;
    }

    /**
     * @return $this
     */
    public function permutation()
    {
        $this->type = SubproductionUnfoldableType::permutation();
        return $this;
    }

    /**
     * @return $this
     */
    public function optional()
    {
        $this->type = SubproductionUnfoldableType::optional();
        return $this;
    }

    /**
     * @return $this
     */
    public function deepUnfold()
    {
        $this->type = SubproductionUnfoldableType::deepUnfold();
        return $this;
    }

    /**
     * @return $this
     */
    public function withFoldingModifier(FoldingModifier $modifier = null)
    {
        $this->foldingModifier = $modifier;
        return $this;
    }

    /**
     * @return $this
     */
    public function withSubproduction(Subproduction $subproduction)
    {
        $this->subproduction = $subproduction;
        return $this;
    }

    /**
     * @param Subproduction|\Polygen\Grammar\Atom\UnfoldableAtom
     * @return $this
     */
    public function withContents($content)
    {
        Assert::object($content);
        if ($content instanceof Token && $content->getType() === Type::nonTerminatingSymbol()) {
            return $this->withNonTerminatingToken($content);
        } else if ($content instanceof Subproduction) {
            return $this->withSubproduction($content);
        }
        throw new \InvalidArgumentException(
            sprintf("Unfoldable token or Subproduction expected, instance of '%s' given instead.", get_class($content))
        );
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
        if ($this->nonTerminatingToken) {
            return new NonTerminatingSymbol(
                $this->nonTerminatingToken,
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
                    $this->foldingModifier
                );
        }
       throw new \LogicException("Unexpected unfoldable type: {$this->type}.");
    }
}
