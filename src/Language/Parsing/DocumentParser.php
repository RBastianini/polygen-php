<?php

namespace Polygen\Language\Parsing;

use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom;
use Polygen\Grammar\Atom\AtomBuilder;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Definition;
use Polygen\Grammar\FoldingModifier;
use Polygen\Grammar\FrequencyModifier;
use Polygen\Grammar\Interfaces\DeclarationInterface;
use Polygen\Grammar\Label;
use Polygen\Grammar\LabelSelection;
use Polygen\Grammar\Production;
use Polygen\Grammar\ProductionCollection;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\Unfoldable\Unfoldable;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\Document;
use Polygen\Language\Exceptions\Parsing\NoAtomFoundException;
use Polygen\Language\Exceptions\Parsing\UnexpectedTokenException;
use Polygen\Language\Token\Type;
use Polygen\Utils\LabelSelectionCollection;

/**
 * Parser for a Polygen document in abstract syntax.
 */
class DocumentParser extends Parser
{
    /**
     * @return Document
     */
    public function parse()
    {
        $declarations = [];

        while (!$this->isEndOfDocument()) {
            $declarations[] = $this->matchDeclaration();
        }
        return new Document($declarations);
    }

    /**
     * @return DeclarationInterface|null
     */
    private function tryMatchDeclaration()
    {
        $this->createSavePoint();
        $this->readTokenIfType(Type::nonTerminatingSymbol());
        $declaration = $this->readTokenIfType(Type::definition(), Type::assignment());
        $this->rollback();
        return $declaration ? $this->matchDeclaration() : null;
    }

    /**
     * @return DeclarationInterface
     */
    private function matchDeclaration()
    {
        $name = $this->readToken(Type::nonTerminatingSymbol());
        $declaration = $this->readToken(Type::definition(), Type::assignment());
        $productions = $this->matchProductions();
        $this->readToken(Type::semicolon());
        switch ($declaration->getType()) {
            case Type::definition():
                return new Definition($name->getValue(), $productions);
            case Type::assignment():
                return new Assignment($name->getValue(), $productions);
        }
        throw new \LogicException('How did you get here?'); // @codeCoverageIgnore
    }

    /**
     * @return ProductionCollection
     */
    private function matchProductions()
    {
        $productions = [];
        do {
            $productions[] = $this->matchProduction();
        } while ($this->readTokenIfType(Type::pipe()));
        return new ProductionCollection($productions);
    }

    /**
     * @return Production
     */
    private function matchProduction()
    {
        $frequencyModifier = $this->tryMatchFrequencyModifiers();
        $sequence = $this->matchSequence();
        return new Production($sequence, $frequencyModifier);
    }

    /**
     * @return Sequence
     */
    private function matchSequence()
    {
        $this->createSavePoint();
        $label = $this->tryMatchLabelWithModifiers();
        // Labels must be followed by columns, if we don't find a column, we took an atom for a label.
        if ($label && !$this->readTokenIfType(Type::colon())) {
            $this->rollback();
            $label = null;
        }

        $sequence = [];
        do {
            $atoms = $this->matchAtomSequence();
            if ($atoms) {
                $sequence[] = $atoms;
            }
        } while ($atoms);

        if (count($sequence) === 0) {
            throw new NoAtomFoundException($this->peek());
        }
        return new Sequence($sequence, $label);
    }

    /**
     * Matches atoms with (optional) implicit positional labels.
     *
     * @return AtomSequence|Atom|null
     */
    private function matchAtomSequence()
    {
        $atoms = [];
        do {
            $atom = $this->tryMatchAtom();
            if ($atom) {
                $atoms[] = $atom;
            }
        } while ($atom && $this->readTokenIfType(Type::comma()));
        switch (count($atoms)) {
            case 0:
                return null;
            case 1:
                return $atom;
            default:
                return new AtomSequence($atoms);
        }
    }

    /**
     * @return Atom|null
     */
    private function tryMatchAtom()
    {
        $atomBuilder = AtomBuilder::get();
        if ($easyMatch = $this->readTokenIfType(
            Type::terminatingSymbol(),
            Type::cap(),
            Type::underscore(),
            Type::backslash()
        )) {
            $atomBuilder->withToken($easyMatch);
        } elseif ($foldingModifier = $this->readTokenIfType(Type::folding(), Type::unfolding())) {
            $atomBuilder->withUnfoldable(
                UnfoldableBuilder::like($this->tryMatchUnfoldable())
                    ->withFoldingModifier(FoldingModifier::fromToken($foldingModifier))
                    ->build()
            );
        } elseif ($unfoldable = $this->tryMatchUnfoldable()) {
            $atomBuilder->withUnfoldable($unfoldable);
        } else {
            return null;
        }

        $labelSelections = [];
        while ($this->isNextTokenOfType(Type::dotLabel(), Type::leftDotBracket(), Type::dot())) {
            if ($dotLabel = $this->readTokenIfType(Type::dotLabel())) {
                $labelSelections[] = LabelSelection::forLabel(new Label($dotLabel->getValue()));
            } elseif ($this->readTokenIfType(Type::leftDotBracket())) {
                $labels = $this->matchMultipleLabels();
                $this->readToken(Type::rightBracket());
                $atomBuilder->withLabelSelections(new LabelSelectionCollection($labelSelections));
                $labelSelections[] = LabelSelection::forLabels($labels);
            } elseif ($this->readTokenIfType(Type::dot())) {
                $labelSelections[] = LabelSelection::reset();
            }
        }
        $atomBuilder->withLabelSelections(new LabelSelectionCollection($labelSelections));
        return $atomBuilder->build();
    }

    /**
     * The reason why we "try" to match an unfoldable, instead of failing if we can't match one, is because
     * the only method that calls it is a "try" method.
     *
     * @return Unfoldable|null
     */
    private function tryMatchUnfoldable()
    {
        if ($nonTerminatingSymbol = $this->readTokenIfType(Type::nonTerminatingSymbol())) {
            return UnfoldableBuilder::get()->withNonTerminatingToken($nonTerminatingSymbol)->build();
        }
        $leftBracket = $this->readTokenIfType(
            Type::leftBracket(),
            Type::leftSquareBracket(),
            Type::leftCurlyBracket(),
            Type::leftDeepUnfolding()
        );
        if ($leftBracket === null) {
            return null;
        }
        $mid = $this->matchSubproduction();
        $unfoldableBuilder = UnfoldableBuilder::get()->withSubproduction($mid);
        $leftBracketType = (string) $leftBracket->getType();
        $rightBracketType = str_replace('LEFT', 'RIGHT', $leftBracketType);
        $this->readToken(Type::ofKind($rightBracketType));
        switch($leftBracketType) {
            case Type::leftBracket():
                if ($this->readTokenIfType(Type::plus())) {
                    return $unfoldableBuilder->iteration()->build();
                }
                return $unfoldableBuilder->simple()->build();
            case Type::leftSquareBracket():
                return $unfoldableBuilder->optional()->build();
            case Type::leftCurlyBracket():
                return $unfoldableBuilder->permutation()->build();
            case Type::leftDeepUnfolding():
                return $unfoldableBuilder->deepUnfold()->build();
        }
        throw new \LogicException('How did you get here?'); // @codeCoverageIgnore
    }

    /**
     * @return Subproduction
     */
    private function matchSubproduction()
    {
        $declarations = [];
        // Match declarations, if there are
        while ($declaration = $this->tryMatchDeclaration()) {
            $declarations[] = $declaration;
        }
        $productions = $this->matchProductions();
        return new Subproduction($declarations, $productions);
    }

    /**
     * @return Label|null
     */
    private function tryMatchLabelWithModifiers()
    {
        $this->createSavePoint();
        $modifier = $this->tryMatchFrequencyModifiers();
        if ($modifier) {
            $label = $this->readToken(Type::nonTerminatingSymbol(), Type::terminatingSymbol());
        } else {
            $label = $this->readTokenIfType(Type::nonTerminatingSymbol(), Type::terminatingSymbol());
        }
        if (!$label) {
            $this->rollback();
        }
        return $label
            ? new Label($label->getValue(), $modifier)
            : null;
    }

    /**
     * @return Label[]
     */
    private function matchMultipleLabels()
    {
        $labels = [];
        do {
            $labels[] = $this->matchLabelWithModifiers();
        } while ($this->readTokenIfType(Type::pipe()));
        return $labels;
    }

    /**
     * @return Label
     */
    private function matchLabelWithModifiers()
    {
        $label = $this->tryMatchLabelWithModifiers();
        if (!$label) {
            $nextToken = $this->peek();
            throw new UnexpectedTokenException(
                [Type::plus(), Type::minus(), Type::nonTerminatingSymbol(), Type::terminatingSymbol()],
                $nextToken->getToken(),
                $nextToken->getPosition()
            );
        }
        return $label;
    }

    /**
     * @return FrequencyModifier|null
     */
    private function tryMatchFrequencyModifiers()
    {
        $modifiersByType = [
            (string) Type::plus() => 0,
            (string) Type::minus() => 0
        ];

        $found = false;
        while ($modifierToken = $this->readTokenIfType(Type::plus(), Type::minus())) {
            $modifiersByType[$modifierToken->getType()->__toString()]++;
            $found = true;
        }

        return $found
            ? new FrequencyModifier(
                $modifiersByType[Type::plus()->__toString()],
                $modifiersByType[Type::minus()->__toString()]
            )
            : null;
    }
}
