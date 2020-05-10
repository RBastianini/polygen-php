<?php

namespace Polygen\Language\Interpretation;

use Polygen\Grammar\Assignment;
use Polygen\Language\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Subclass of the declaration context that handles assignments of assignments.
 * This declaration context knows which assignments have been resolved and which Token array they have produced, and
 * ties this information to the DeclarationsContext where the assignment happened, thus allowing narrower contexts to
 * reassign previously assigned assignments.
 */
class DeclarationsContext extends \Polygen\Language\Preprocessing\StaticCheck\InfiniteRecursionCheck\DeclarationsContext
{
    /**
     * @var Token[][]
     */
    private $assignedAssignments = [];

    /**
     * @param \Polygen\Grammar\Assignment $assignment
     * @param Token[] $tokens
     */
    public function assign(Assignment $assignment, array $tokens)
    {
        if ($this->ownsDeclaration($assignment->getName())) {
            Assert::allIsInstanceOf($tokens, Token::class);
            Assert::false($this->isAssigned($assignment), "Trying to assign {$assignment->getName()} twice.");
            $this->assignedAssignments[$assignment->getName()] = $tokens;
        } else if ($this->parentContext !== null) {
            $this->parentContext->assign($assignment, $tokens);
        } else {
            throw new \RuntimeException("{$assignment->getName()} seems not to belong to any declaration context.");
        }
    }

    /**
     * @return bool
     */
    public function isAssigned(Assignment $assignment)
    {
        if ($this->ownsDeclaration($assignment->getName())) {
            return array_key_exists($assignment->getName(), $this->assignedAssignments);
        } else if ($this->parentContext !== null) {
            return $this->parentContext->isAssigned($assignment);
        }
        return false;
    }

    /**
     * @return Token[]
     */
    public function getAssigned(Assignment $assignemnt)
    {
        if (array_key_exists($assignemnt->getName(), $this->assignedAssignments)) {
            return $this->assignedAssignments[$assignemnt->getName()];
        } else if ($this->parentContext !== null) {
            return $this->parentContext->getAssigned($assignemnt);
        }
        throw new \RuntimeException("{$assignemnt->getName()} was never assigned.");
    }
}
