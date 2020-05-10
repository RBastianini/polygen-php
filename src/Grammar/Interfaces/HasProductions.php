<?php

namespace Polygen\Grammar\Interfaces;

use Polygen\Grammar\Production;
use Polygen\Grammar\ProductionCollection;

/**
 * Interface for nodes having productions.
 */
interface HasProductions
{
    /**
     * @deprecated
     * @return Production[]
     */
    public function getProductions();

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
