<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Atom;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Production;
use Polygen\Grammar\ProductionCollection;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Utils\DeclarationCollection;

/**
 * Bonus conversion step: This is not mentioned in the official Polygen guide, as it deals with a syntax that is never
 * described in the formal language representations, but that it is indeed supported. The goal is to convert
 * Something.label1.(label2|label3).label4..label5
 * (where the double dot is not a mistake) into
 * (((((Something).label1).(label2|label3)).label4).).label5
 */
class MultipleLabelSelectionGroupConverter implements ConverterInterface
{
    /**
     * @return int
     */
    public function getPriority()
    {
        return -1;
    }

    /**
     * @param Atom|Node $node
     * @return Node
     */
    public function convert(Node $node, DeclarationCollection $context)
    {
        $nestedSelection = null;
        $labelSelections = $node->getLabelSelections()->getLabelSelections();
        $nestedSelection = Atom\AtomBuilder::like($node)->withLabelSelection(array_shift($labelSelections))->build();
        foreach ($labelSelections as $labelSelection) {
            $nestedSelection = Atom\AtomBuilder::get()
                ->withUnfoldable(
                    UnfoldableBuilder::get()
                        ->simple()
                        ->withContents(
                            new Subproduction(
                                [],
                                new ProductionCollection([
                                    new Production(
                                        new Sequence([
                                            $nestedSelection
                                        ])
                                    )
                                ])
                            )
                        )
                        ->build()
                    )
                ->withLabelSelection($labelSelection)
                ->build();
        }
        return $nestedSelection;
    }

    /**
     * @return bool
     */
    public function canConvert(Node $node)
    {
        return $node instanceof Atom && $node->getLabelSelections()->count() > 1;
    }
}
