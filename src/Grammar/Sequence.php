<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\Labelable;
use Polygen\Grammar\Interfaces\Labeled;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen Sequence.
 */
class Sequence implements Node, Labeled
{
    /**
     * @var Label
     */
    private $label;

    /**
     * @var AtomSequence[]
     */
    private $atomSequences;

    /**
     * Sequence constructor.
     *
     * @param \Polygen\Grammar\AtomSequence[] $atoms
     */
    public function __construct(array $atoms, Label $label = null)
    {
        foreach ($atoms as $atom) {
            Assert::true(
                $atom instanceof AtomSequence || $atom instanceof Labelable,
                'Bad input to Sequence constructor: '
                . ((is_object($atom) ? get_class($atom) : gettype($atom)))
            );
        }
        $this->label = $label;
        $this->atomSequences = $atoms;
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @return mixed
     */
    public function traverse(AbstractSyntaxWalker $walker)
    {
        return $walker->walkSequence($this);
    }

    /**
     * @return Label[]
     */
    public function getSelectedLabels()
    {
        return [$this->label];
    }

    /**
     * @return bool
     */
    public function isResetLabel()
    {
        return false;
    }

    public function getLabelables()
    {
        return $this->atomSequences;
    }
}
