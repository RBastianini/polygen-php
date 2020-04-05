<?php

namespace Polygen\Grammar\Interfaces;

use Polygen\Grammar\Production;

/**
 * Interface for nodes having productions.
 */
interface HasProductions
{
    /**
     * @return Production[]
     */
    public function getProductions();

    /**
     * Returns a new instance of this object with the same properties, but with the specified productions.
     *
     * @param Production[] $productions
     * @return static
     */
    public function withProductions(array $productions);
}
