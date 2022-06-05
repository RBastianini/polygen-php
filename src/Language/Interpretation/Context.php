<?php

namespace Polygen\Language\Interpretation;

use Polygen\Grammar\Assignment;
use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\LabelSelection;
use Polygen\Language\Document;
use Polygen\Language\Token\Token;
use Polygen\Utils\DeclarationCollection;
use Polygen\Utils\LabelSelectionCollection;
use Savvot\Random\AbstractRand;
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
     * @var AbstractRand
     */
    private $randomNumberGenerator;

    /**
     * @var DeclarationsContext
     */
    private $declarationsContext;

    /**
     * @var static
     */
    private $parentContext;

    /**
     * @param string $startSymbol The declaration from which the production should start.
     * @param string $seed The random number generator seed.
     * @return static
     */
    public static function get($startSymbol = Document::START, $seed = null, LabelSelection $labelSelection = null)
    {
        return new static(
            DeclarationsContext::root(new DeclarationCollection()),
            $startSymbol,
            new MtRand($seed),
            $labelSelection ?: LabelSelection::none(),
            null
        );
    }

    /**
     * @param string $startSymbol The declaration from which the production should start.
     * @param string $seed The random number generator seed.
     */
    private function __construct(
        DeclarationsContext $declarationContext,
        $startSymbol,
        AbstractRand $randomNumberGenerator,
        LabelSelection $labelSelection,
        Context $parentContext = null
    ) {
        $this->declarationsContext = $declarationContext;
        $this->randomNumberGenerator = $randomNumberGenerator;
        $this->startSymbol = $startSymbol;
        $this->parentContext = $parentContext;
        $this->labelSelection = $labelSelection;
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
        return $min === $max
            ? $min
            : $this->randomNumberGenerator->random($min, $max);
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
     * @return static
     */
    public function select(LabelSelectionCollection $labelSelectionCollection)
    {
        $result = $this;
        foreach ($labelSelectionCollection->getLabelSelections() as $labelSelection) {
            $result = $result
                ? $result->doSelect($labelSelection)
                : $this->doSelect($labelSelection);
        }
        return $result;
    }

    private function doSelect(LabelSelection $labelSelection)
    {
        if ($labelSelection->isEmpty()) {
            $newSelection = $this->labelSelection;
        } elseif ($labelSelection->isLabelResetting()) {
            $newSelection = LabelSelection::none();
        } else {
            $newSelection = $this->labelSelection->add($labelSelection->getRandomLabel($this));
        }
        return new static(
            $this->declarationsContext,
            $this->startSymbol,
            $this->randomNumberGenerator,
            $newSelection,
            $this
        );
    }

    /**
     * @return static
     */
    public function getContextForDeclaration(DeclarationInterface $declaration)
    {
        if ($this->declarationsContext->ownsDeclaration($declaration->getName())) {
            return $this;
        } else if ($this->parentContext !== null) {
            return $this->parentContext->getContextForDeclaration($declaration);
        }
        throw new \RuntimeException("No suitable context found for declaration {$declaration->getName()}.");
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
     * @return string
     */
    public function getSeed()
    {
        return $this->randomNumberGenerator->getSeed();
    }
}
