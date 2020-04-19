<?php

namespace Polygen\Grammar\Atom;

use Polygen\Grammar\Atom;
use Polygen\Grammar\LabelSelection;
use Polygen\Grammar\Unfoldable\Unfoldable;
use Polygen\Language\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Builder class to assemble Atoms.
 */
class AtomBuilder
{
    /**
     * @var Token|null
     */
    private $token;

    /**
     * @var Unfoldable|null
     */
    private $unfoldable;

    /**
     * @var LabelSelection|null
     */
    private $labelSelection;

    private function __construct(
        Token $token = null,
        Unfoldable $unfoldable = null,
        LabelSelection $labelSelection = null
    ) {
        $this->token = $token;
        $this->unfoldable = $unfoldable;
        $this->labelSelection = $labelSelection ?: LabelSelection::none();
    }

    /**
     * Returns a new empty builder.
     *
     * @return static
     */
    public static function get() {
        return new static();
    }

    /**
     * Returns a new builder preconfigured to produce the passed atom.
     *
     * @return static
     */
    public static function like(Atom $atom)
    {
        return new static(
            $atom instanceof SimpleAtom ? $atom->getToken() : null,
            $atom instanceof UnfoldableAtom ? $atom->getUnfoldable() : null,
            $atom->getLabelSelection()
        );
    }

    /**
     * @return $this
     */
    public function withToken(Token $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return $this
     */
    public function withUnfoldable(Unfoldable $unfoldable)
    {
        $this->unfoldable = $unfoldable;
        return $this;
    }

    /**
     * @return $this
     */
    public function withLabelSelection(LabelSelection $labelSelection)
    {
        $this->labelSelection = $labelSelection;
        return $this;
    }

    /**
     * @return Atom
     */
    public function build()
    {
        Assert::true(
            $this->token === null xor $this->unfoldable === null,
            'You have to specify either a token or an unfoldable to build an atom.'
        );

        if ($this->token) {
            return new SimpleAtom($this->token, $this->labelSelection);
        }
        return new UnfoldableAtom($this->unfoldable, $this->labelSelection);
    }
}
