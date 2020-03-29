<?php

namespace Tests\Integration\Language\Preprocessing\ConcreteToAbstractConversion;

use Polygen\Language\Preprocessing\ConcreteToAbstractConversion\ConverterInterface;
use Polygen\Language\Preprocessing\AbstractToConcreteSyntaxConverter;

/**
 * Utility trait for testing converters in isolation, while still relying on the worker for the document traversal.
 */
trait ConverterUtils
{
    private function given_a_converter_with(ConverterInterface ...$converters)
    {
        return new AbstractToConcreteSyntaxConverter($converters);
    }
}
