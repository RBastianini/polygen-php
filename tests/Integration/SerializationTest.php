<?php

namespace Tests\Polygen\Integration;

use Polygen\Language\Interpretation\Context;
use Polygen\Polygen;
use Tests\StreamUtils;
use Tests\TestCase;

class SerializationTest extends TestCase
{
    use StreamUtils;

    /**
     * @test
     */
    public function it_can_parse_an_unserialized_document()
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument($this->given_a_source_file(__DIR__ . '/../files/incredible-commit.grm'));

        $serialized_document = serialize($document);

        $unserialized_document = unserialize($serialized_document);

        $string = (new Polygen())->generate($unserialized_document, $context = Context::get());

        $this->assertRegExp('{^[0-9a-f]{7} ELD-[^0][0-9]*:}', $string, $context->getSeed());
    }
}
