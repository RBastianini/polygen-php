<?php

namespace Polygen\Language\Interpretation;

use Polygen\Grammar\Assignment;
use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\LabelSelection;
use Polygen\Language\Document;
use Polygen\Language\Token\Token;
use Polygen\Utils\DeclarationCollection;
use Savvot\Random\MtRand;
use Webmozart\Assert\Assert;

/**
 * Object representing the current status during Polygen sentence generation.
 */
class Context
{
    /**
     * The symbol from which generation should start.
     * @var string
     */
    private $startSymbol;

    /**
     * @var LabelSelection
     */
    private $labelSelection;

    /**
     * @var MtRand
     */
    private $randomNumberGenerator;
    /**
     * @var DeclarationsContext
     */
    private $declarationsContext;

    /**
     * @param string $startSymbol The declaration from which the production should start.
     * @param string $seed The random number generator seed.
     */
    public function __construct($startSymbol = Document::START, $seed = null)
    {
        $this->declarationsContext = DeclarationsContext::root(new DeclarationCollection());
        $this->randomNumberGenerator = new MtRand($seed);
        $this->startSymbol = $startSymbol;
        $this->labelSelection = LabelSelection::none();
    }

    /**
     * @param Token[] $tokens
     */
    public function assign(Assignment $assignment, array $tokens)
    {
        $this->declarationsContext->assign($assignment, $tokens);
    }

    /**
     * Checks whether an assignment has already been processed and its generated value has been stored in the context.
     *
     * @return bool
     */
    public function isAssigned(Assignment $assignment)
    {
        return $this->declarationsContext->isAssigned($assignment);
    }

    /**
     * Returns the token sequence for an already processed assignment.
     *
     * @return Token[]
     */
    public function getAssigned(Assignment $assignment)
    {
        return $this->declarationsContext->getAssigned($assignment);
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     */
    public function getRandomNumber($min, $max)
    {
        Assert::integer($min);
        Assert::greaterThanEq($min, 0);
        Assert::integer($max);
        Assert::greaterThanEq($max, 0);
        return $this->randomNumberGenerator->random($min, $max);
    }

    /**
     * Returns the start symbol from which generation should take place.
     *
     * @return string
     */
    public function getStartSymbol()
    {
        return $this->startSymbol;
    }

    /**
     * @return Context
     */
    public function select(LabelSelection $labelSelection)
    {
        $clone = clone $this;
        $clone->labelSelection = $this->labelSelection->merge($labelSelection);
        return $clone;
    }

    /**
     * @return LabelSelection
     */
    public function getLabelSelection()
    {
        return $this->labelSelection;
    }

    /**
     * @param string $declaration
     * @return bool
     */
    public function isDeclared($declaration)
    {
        return $this->declarationsContext->isDeclared($declaration);
    }

    /**
     * @param DeclarationInterface[] $declarations
     * @return static
     */
    public function mergeDeclarations(array $declarations)
    {
        $clone = clone $this;
        $clone->declarationsContext = $this->declarationsContext->addDeclarations(
            new DeclarationCollection($declarations)
        );
        return $clone;
    }

    /**
     * @param string $declarationName
     * @return DeclarationInterface|Node
     */
    public function getDeclaration($declarationName)
    {
        return $this->declarationsContext->getDeclaration($declarationName);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->declarationsContext->isEmpty();
    }

    /**
     * @return string
     */
    public function getSeed()
    {
        return $this->randomNumberGenerator->getSeed();
    }
}
