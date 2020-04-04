<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\Labelable;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\Token\Token;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Atom Polygen node.
 */
class Atom implements Labelable, Node
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

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @return mixed
     */
    public function traverse(AbstractSyntaxWalker $walker)
    {
        return $walker->walkAtom($this);
    }

    /**
     * @return boolean
     */
    public function hasLabels()
    {
        return !empty($this->labels);
    }

    /**
     * @return Label[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Returns a copy of the current node, with no labels.
     *
     * @return static
     */
    public function withoutLabels()
    {
        return new static($this->token);
    }
}
