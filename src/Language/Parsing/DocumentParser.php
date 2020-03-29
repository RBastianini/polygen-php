<?php

namespace Polygen\Language\Parsing;

use Polygen\Document;
use Polygen\Grammar\Assignment;
use Polygen\Grammar\Atom;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\Definition;
use Polygen\Grammar\FoldingModifier;
use Polygen\Grammar\Interfaces\Labelable;
use Polygen\Grammar\Label;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\SubProduction;
use Polygen\Grammar\Unfoldable;
use Polygen\Language\Exceptions\Parsing\UnexpectedTokenException;
use Polygen\Language\Token\Type;
use Webmozart\Assert\Assert;

/**
 * Parser for a Polygen document in abstract syntax.
 *
 * @todo Yes this is a monstruosity of a class parsing the entire document and practically untestable.
 */
class DocumentParser extends Parser
{
    /**
     * @return \Polygen\Document
     */
    public function parse()
    {
        $definitions = [];
        $assignments = [];

        while (!$this->isEndOfDocument()) {
            $definitionOrAssignment = $this->matchDeclaration();
            if ($definitionOrAssignment instanceof Definition) {
                $definitions[] = $definitionOrAssignment;
            } else {
                $assignments[] = $definitionOrAssignment;
            }
        }
        return new Document($definitions, $assignments);
    }

    /**
     * @return \Polygen\Grammar\Assignment|\Polygen\Grammar\Definition|null
     */
    private function tryMatchDeclaration()
    {
        $this->createSavePoint();
        $this->readTokenIfType(Type::nonTerminatingSymbol());
        $declarationOrAssignmentSymbol = $this->readTokenIfType(Type::definition(), Type::assignment());
        $this->rollback();
        return $declarationOrAssignmentSymbol ? $this->matchDeclaration() : null;
    }

    private function matchDeclaration()
    {
        $name = $this->readToken(Type::nonTerminatingSymbol());
        $declarationOrAssignmentSymbol = $this->readToken(Type::definition(), Type::assignment());
        $productions = $this->matchProductions();
        $this->readToken(Type::semicolon());
        switch ($declarationOrAssignmentSymbol->getType()) {
            case Type::definition():
                return new Definition($name->getValue(), $productions);
                break;
            case Type::assignment():
                return new Assignment($name->getValue(), $productions);
                break;
        }
        throw new \LogicException('How did you get here?');
    }

    private function matchProductions()
    {
        $productions = [];
        do {
            $productions[] = $this->matchProduction();
        } while ($this->readTokenIfType(Type::pipe()));
        return $productions;
    }

    private function matchProduction()
    {
        $modifiers = $this->tryMatchFrequencyModifiers();
        $sequence = $this->matchSequence();
        return new Production($modifiers, $sequence);
    }

    private function matchSequence()
    {
        $this->createSavePoint();
        $label = $this->tryMatchLabelWithModifiers();
        // Labels must be followed by columns, if we don't find a column, we took an atom for a label.
        if ($label && !$this->readTokenIfType(Type::colon())) {
            $this->rollback();
            $label = null;
        }
        // Fixme there's something fishy here
        $atomSequence = [];
        do {
            $atoms = $this->matchAtomSequence();
            if ($atoms) {
                $atomSequence[] = $atoms;
            }
        } while ($atoms);

        Assert::greaterThan(count($atomSequence), 0, "Expected to match atom sequence, but no atom found. {$this->peek()} found instead.");
        return new Sequence($atomSequence, $label);
    }

    /**
     * Matches atoms with (optional) implicit positional labels.
     *
     * @return AtomSequence|null
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
        return $atoms
            ? new AtomSequence($atoms)
            : null;
    }

    /**
     * @return Labelable
     */
    private function tryMatchAtom()
    {
        $atomToReturn = null;
        if ($easyMatch = $this->readTokenIfType(
            Type::terminatingSymbol(),
            Type::cap(),
            Type::underscore(),
            Type::backslash()
        )) {
            $atomToReturn = Atom::simple($easyMatch);
        } elseif ($foldingModifier = $this->readTokenIfType(Type::folding(), Type::unfolding())) {
            $atomToReturn = $this->tryMatchUnfoldable()->withFoldingModifier(FoldingModifier::fromToken($foldingModifier));
        } else {
            $atomToReturn = $this->tryMatchUnfoldable();
        }

        if ($atomToReturn === null) {
            return null;
        }

        if ($dotLabel = $this->readTokenIfType(Type::dotLabel())) {
            $atomToReturn = $atomToReturn->withLabel(new Label($dotLabel));
        } elseif ($this->readTokenIfType(Type::leftDotBracket())) {
            $labels = $this->matchMultipleLabels();
            $this->readToken(Type::rightBracket());
            $atomToReturn = $atomToReturn->withLabels($labels);
        } elseif ($this->readTokenIfType(Type::dot())) {
            $atomToReturn = $atomToReturn->withLabelSelectionResetToggle();
        }
        return $atomToReturn;
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
            return Unfoldable::nonTerminating($nonTerminatingSymbol);
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
        $mid = $this->matchSubProduction();
        $leftBracketType = (string)$leftBracket->getType();
        $rightBracketType = str_replace('LEFT', 'RIGHT', $leftBracketType);
        $this->readToken(Type::ofKind($rightBracketType));
        switch($leftBracketType) {
            case Type::leftBracket():
                if ($this->readTokenIfType(Type::plus())) {
                    return Unfoldable::iterate($mid);
                }
                return Unfoldable::simple($mid);
            case Type::leftSquareBracket():
                return Unfoldable::optional($mid);
            case Type::leftCurlyBracket():
                return Unfoldable::permutate($mid);
            case Type::leftDeepUnfolding():
                return Unfoldable::deepUnfold($mid);
        }
        throw new \LogicException('How did you get here?');
    }


    private function matchSubProduction()
    {
        $declarationOrAssignments = [];
        // Match declarations, if there are
        while ($declaration = $this->tryMatchDeclaration()) {
            $declarationOrAssignments[] = $declaration;
        }
        $productions = $this->matchProductions();
        return new SubProduction($declarationOrAssignments, $productions);
    }

    private function tryMatchLabelWithModifiers()
    {
        $this->createSavePoint();
        $modifiers = $this->tryMatchFrequencyModifiers();
        if ($modifiers) {
            $label = $this->readToken(Type::nonTerminatingSymbol(), Type::terminatingSymbol());
        } else {
            $label = $this->readTokenIfType(Type::nonTerminatingSymbol(), Type::terminatingSymbol());
        }
        if (!$label) {
            $this->rollback();
        }
        return $label ? new Label($label, $modifiers) : null;
    }

    private function matchMultipleLabels()
    {
        $labels = [];
        do {
            $labels[] = $this->matchLabelWithModifiers();
        } while ($this->readTokenIfType(Type::pipe()));
        return $labels;
    }

    private function matchLabelWithModifiers()
    {
        $label = $this->tryMatchLabelWithModifiers();
        if (!$label) {
            throw new UnexpectedTokenException(
                [Type::plus(), Type::minus(), Type::nonTerminatingSymbol(), Type::terminatingSymbol()],
                $this->peek()
            );
        }
        return $label;
    }

    /**
     * @return \Polygen\Grammar\FrequencyModifier[]
     */
    private function tryMatchFrequencyModifiers()
    {
        $modifierCount = 0;
        $modifierType = null;
        while ($modifierToken = $this->readTokenIfType(Type::plus(), Type::minus())) {
            if ($modifierType !== null) {
                $modifierType = $modifierToken;
            } else {
                Assert::eq($modifierToken, $modifierType, "Can't mix plus and minus modifiers together.");
            }
            $modifierCount++;
        }
        $modifier = null;
        if ($modifierType !== null) {
            $modifier = $modifierType === Type::plus()
                ? \Polygen\Grammar\FrequencyModifier::plus()
                : \Polygen\Grammar\FrequencyModifier::minus();
        }
        return array_fill(
            0,
            $modifierCount,
            $modifier
        );
    }
}
