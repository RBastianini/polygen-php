<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\Labelable;
use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;
use Webmozart\Assert\Assert;

/**
 *
 */
class Atom implements Labelable
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var Label[]
     */
    private $labels = [];

    /**
     * @var null
     */
    private $foldingModifier;

    /**
     * Whe toggled to true, when resolving this atom, all previously selected labels will be unselected.
     *
     * @var bool
     */
    private $resetLabelSelection = false;

    /**
     * Atom constructor.
     *
     * @param Token $token
     * @param Label[] $labels
     * @param null $foldingModifier
     */
    private function __construct(Token $token)
    {
        $this->token = $token;
    }

    /**
     * @param Token $token
     * @return static
     */
    public static function simple(Token $token)
    {
        return new static($token);
    }

    /**
     * @param $token
     * @param $label
     * @return static
     */
    public function withLabel(Label $label)
    {
        return $this->withLabels([$label]);
    }

    /**
     * @param Label[] $labels
     * @return static
     */
    public function withLabels(array $labels)
    {
        Assert::isEmpty($this->labels);
        Assert::allIsInstanceOf($labels, Label::class);
        $this->labels = $labels;
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
}
