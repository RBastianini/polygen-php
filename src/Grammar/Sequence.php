<?php

namespace Polygen\Grammar;

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
     * @var AtomSequence[]|Atom[]
     */
    private $atomSequences;

    /**
     * Sequence constructor.
     *
     * @param \Polygen\Grammar\AtomSequence[]|Atom[] $atoms
     */
    public function __construct(array $atoms, Label $label = null)
    {
        foreach ($atoms as $atom) {
            Assert::true(
                $atom instanceof AtomSequence || $atom instanceof Atom,
                'Bad input to Sequence constructor: '
                . ((is_object($atom) ? get_class($atom) : gettype($atom)))
            );
        }
        $this->label = $label;
        $this->atomSequences = array_values($atoms);
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkSequence($this, $context);
    }

    /**
     * @return Label|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return AtomSequence[]|Atom[]
     */
    public function getSequenceContents()
    {
        return $this->atomSequences;
    }
}
