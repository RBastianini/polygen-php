<?php

namespace Polygen\Grammar\Atom;

use Polygen\Grammar\Atom;
use Polygen\Grammar\LabelSelection;
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Token\Token;

/**
 * An atom containing a terminating symbol of some sorts.
 */
class SimpleAtom extends Atom
{
    /**
     * @var Token
     */
    private $token;

    public function __construct(Token $token, LabelSelection $labelSelection = null)
    {
        parent::__construct($labelSelection ?: LabelSelection::none());
        $this->token = $token;
    }

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkSimpleAtom($this, $context);
    }
}
