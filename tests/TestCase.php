<?php

namespace Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 *
 */
class TestCase extends PHPUnitTestCase
{
    use MockeryPHPUnitIntegration;
}
