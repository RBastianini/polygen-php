<?php

namespace Polygen\Grammar\Atom;

use Polygen\Grammar\Atom;
use Polygen\Grammar\LabelSelection;
use Polygen\Language\Token\Token;

/**
 *
 */
class SimpleAtom extends Atom
{
    /**
     * @var Token
     */
    private $token;

    public function __construct(Token $token, LabelSelection $labelSelection)
    {
        parent::__construct($labelSelection);
        $this->token = $token;
    }

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }
}
