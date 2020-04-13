<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\HasLabelSelection;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\Token\Token;
use Polygen\Language\AbstractSyntaxWalker;

/**
 * Atom Polygen node.
 */
class Atom implements HasLabelSelection, Node
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var LabelSelection
     */
    private $labelSelection;

    /**
     * Atom constructor.
     *
     * @param Token $token
     * @param Label[] $labels
     * @param null $foldingModifier
     */
    private function __construct(Token $token, LabelSelection $labelSelection)
    {
        $this->token = $token;
        $this->labelSelection = $labelSelection;
    }

    /**
     * @param Token $token
     * @return static
     */
    public static function simple(Token $token)
    {
        return new static($token, LabelSelection::none());
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
     * @return static
     */
    public function withLabelSelection(LabelSelection $labelSelection)
    {
        $clone = clone $this;
        $clone->labelSelection = $labelSelection;
        return $clone;
    }

    /**
     * @return \Polygen\Grammar\LabelSelection
     */
    public function getLabelSelection()
    {
        return $this->labelSelection;
    }
}
