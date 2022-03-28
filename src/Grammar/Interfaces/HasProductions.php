<?php

namespace Polygen\Grammar\Interfaces;

use Polygen\Grammar\ProductionCollection;

/**
 * Interface for nodes having productions.
 */
interface HasProductions
{
    /**
     * Returns a new instance of this object with the same properties, but with the specified productions.
     *
     * @return static
     */
    public function withProductions(ProductionCollection $productionCollection);

    /**
     * @return ProductionCollection
     */
    public function getProductionSet();
}
