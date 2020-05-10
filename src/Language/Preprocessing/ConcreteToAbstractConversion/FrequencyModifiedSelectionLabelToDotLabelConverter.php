<?php

namespace Polygen\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Grammar\Atom;
use Polygen\Grammar\Definition;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Grammar\Label;
use Polygen\Grammar\LabelSelection;
use Polygen\Grammar\Production;
use Polygen\Grammar\ProductionCollection;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\Subproduction;
use Polygen\Grammar\Unfoldable\UnfoldableBuilder;
use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\Services\FrequencyModificationWeightCalculator;
use Polygen\Language\Preprocessing\Services\IdentifierFactory;
use Polygen\Language\Token\Token;
use Polygen\Utils\DeclarationCollection;
use Webmozart\Assert\Assert;

/**
 * The idea of this conversion step is to go from
 * Something.( label1 | +label2 | -label3 | ++label4)
 * to
 * ( X ::= Something; X.label1 | X.label1 | X.label2 | X.label2 | X.label2 | X.label3 | X.label3 | X.label3 | X.label3 )
 *
 * Each label is going to appear at least once. The number of times each label is going to appear is calculated as follows.
 *
 * Let
 * l = [label1, label2, ...]
 * plus(l) = number of plus modifiers that are in front of l
 * minus(l) = number of minus modifiers that are in front of l
 * frequencyModificationWeight(l) = +1 * plus(l) -1 * minus(l)
 * frequencyModificationWeightByLabelPosition[i] = frequencyModificationWeight(l[i])
 *
 * so the number of times that the label will be repeated will be
 * occurrences(l) = 1 + frequencyModificationWeight(l) - min {frequencyModificationWeightByLabelPosition}
 */
class FrequencyModifiedSelectionLabelToDotLabelConverter implements ConverterInterface
{

    /**
     * @var IdentifierFactory
     */
    private $identifierFactory;
    /**
     * @var FrequencyModificationWeightCalculator
     */
    private $frequencyModificationWeightCalculator;

    public function __construct(
        IdentifierFactory $identifierFactory,
        FrequencyModificationWeightCalculator $frequencyModificationWeightCalculator
    ) {
        $this->identifierFactory = $identifierFactory;
        $this->frequencyModificationWeightCalculator = $frequencyModificationWeightCalculator;
    }

    /**
     * The lowest the number, the highest the priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return 1;
    }

    /**
     * @param Atom $node
     * @return Atom
     */
    public function convert(Node $node, DeclarationCollection $_)
    {
        Assert::isInstanceOf($node, Atom::class);

        $frequencyModificationWeightByLabelPosition = $this->frequencyModificationWeightCalculator->getFrequencyModificationWeightByPosition(
            $node->getLabelSelection()->getLabels()
        );

        $definitionName = $this->identifierFactory->getId('Definition');

        $productions = [];
        foreach ($node->getLabelSelection()->getLabels() as $labelIndex => $label) {
            $productions = array_merge(
                $productions,
                array_fill(
                    0,
                    $frequencyModificationWeightByLabelPosition[$labelIndex],
                    new Production(
                        new Sequence(
                            [
                                Atom\AtomBuilder::get()
                                    ->withUnfoldable(
                                        UnfoldableBuilder::get()
                                            ->withNonTerminatingToken(Token::nonTerminatingSymbol($definitionName))
                                            ->build()
                                        )
                                    ->withLabelSelection(
                                        LabelSelection::forLabel($label->withoutFrequencyModifier())
                                    )
                                ->build()
                            ]
                        )
                    )
                )
            );
        }

        $definition = new Definition(
            $definitionName,
            new ProductionCollection(
                [
                    new Production(
                        new Sequence([
                            Atom\AtomBuilder::like($node)
                                ->withLabelSelection(LabelSelection::none())
                                ->build()
                        ])
                    )
                ]
            )
        );
        return Atom\AtomBuilder::get()
            ->withUnfoldable(
                UnfoldableBuilder::get()
                ->simple()
                ->withSubproduction(
                    new Subproduction(
                        [$definition],
                        new ProductionCollection($productions)
                    )
                )
            ->build()
        )
        ->build();
    }

    /**
     * @param Node $node
     * @return bool
     */
    public function canConvert(Node $node)
    {
        return $node instanceof Atom
            && count(array_filter($node->getLabelSelection()->getLabels(), [$this, 'filterLabels'])) > 0;
    }

    private function filterLabels(Label $label) {

        return $label->getFrequencyModifier()->getNetFrequencyChange() !== 0;
    }
}
