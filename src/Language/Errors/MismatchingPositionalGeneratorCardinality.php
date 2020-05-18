<?php

namespace Polygen\Language\Errors;

use Polygen\Grammar\AtomSequence;

class MismatchingPositionalGeneratorCardinality implements Error
{
    /**
     * @var AtomSequence
     */
    private $atomSequence;

    /**
     * @var int
     */
    private $expectedCardinality;

    /**
     * MismatchingPositionalGeneratorCardinality constructor.
     *
     * @param int $previousCardinality
     */
    public function __construct(AtomSequence $atomSequence, $previousCardinality)
    {
        $this->atomSequence = $atomSequence;
        $this->expectedCardinality = $previousCardinality;
    }

    public function getMessage()
    {
        $foundCardinality = count($this->atomSequence->getAtoms());
        return "Mismatching positional sequence generator cardinality. Expected cardinality: " .
            "{$this->expectedCardinality}, found: $foundCardinality.";
    }
}
