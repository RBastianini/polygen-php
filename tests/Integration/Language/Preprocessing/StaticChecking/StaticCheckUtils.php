<?php

namespace Tests\Integration\Language\Preprocessing\StaticChecking;

use Polygen\Language\Preprocessing\StaticCheck\StaticCheckInterface;
use Polygen\Language\Preprocessing\StaticChecker;

/**
 * Collection of utility methods to test static checks.
 */
trait StaticCheckUtils
{
    /**
     * @return \Polygen\Language\Preprocessing\StaticChecker
     */
    private function given_a_static_checker_with(StaticCheckInterface ...$checks)
    {
        return new StaticChecker($checks);
    }
}
