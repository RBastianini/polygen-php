<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Atom\SimpleAtom;
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Interpretation\Context;
use Polygen\Language\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Collection class handy for filtering and selecting productions.
 */
class ProductionCollection
{
    const NO_LABEL = '#no label#';

    private $productions;

    private $productionsBySelection = [self::NO_LABEL => []];

    /**
     * @param Production[] $productions
     */
    public function __construct(array $productions)
    {
        Assert::allIsInstanceOf($productions, Production::class);
        $this->productions = array_values($productions);
        // Currently production results depend on the order in which productions appear. I don't know if this was the
        // case in the original Polygen, but I don't believe this should be the case.
        // The issue is that, uncommenting the following line makes a bunch of tests fail (easy) and a bunch of other
        // tests potentially in need of finding new seeds to still make sure they work (hard).
        // I will uncomment it soon™.
        // sort($this->productions);
        foreach ($productions as $production) {
            $label = $production->getSequence()->getLabel()
                ? $production->getSequence()->getLabel()->getName()
                : self::NO_LABEL;
            $this->productionsBySelection[$label][] = $production;
        }
    }

    public function getProductions()
    {
        return $this->productions;
    }

    /**
     * @return static
     */
    public function whereLabelSelection(LabelSelection $labelSelection)
    {
        if ($labelSelection->isEmpty()) {
            return $this;
        }
        $selectedProductionArrays[] = $this->productionsBySelection[self::NO_LABEL];
        foreach ($labelSelection->getUniqueLabels() as $label) {
            if (array_key_exists($label->getName(), $this->productionsBySelection)) {
                $selectedProductionArrays[] = $this->productionsBySelection[$label->getName()];
            }
        }
        return new static(array_merge(... $selectedProductionArrays));
    }

    /**
     * @return Production
     */
    public function getRandom(Context $context)
    {
        // Return an epsilon-generating production if there's nothing to produce.
        // This can happen when combining multiple label selections, ending up in no possible production to select.
        if (empty($this->productions)) {
            return new Production(new Sequence([new SimpleAtom(Token::underscore(), LabelSelection::none())]));
        }
        return $this->productions[$context->getRandomNumber(0, count($this->productions) - 1)];
    }
}
