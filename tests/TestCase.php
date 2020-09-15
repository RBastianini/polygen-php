<?php

namespace Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test class.
 */
class TestCase extends PHPUnitTestCase
{
    use MockeryPHPUnitIntegration;
}
