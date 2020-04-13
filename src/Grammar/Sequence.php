<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\HasLabelSelection;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen Sequence.
 */
class Sequence implements Node
{
    /**
     * @var Label|null
     */
    private $label;

    /**
     * @var AtomSequence[]|HasLabelSelection[]
     */
    private $atomSequences;

    /**
     * Sequence constructor.
     *
     * @param \Polygen\Grammar\AtomSequence[]|HasLabelSelection[] $atoms
     */
    public function __construct(array $atoms, Label $label = null)
    {
        foreach ($atoms as $atom) {
            Assert::true(
                $atom instanceof AtomSequence || $atom instanceof HasLabelSelection,
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
     * @return Label|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return AtomSequence[]|HasLabelSelection[]
     */
    public function getSequenceContents()
    {
        return $this->atomSequences;
    }
}
