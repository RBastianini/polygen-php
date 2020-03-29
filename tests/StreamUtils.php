<?php

namespace Tests;

use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Stream\StreamInterface;

/**
 * Traits with utility method to test stream consuming objects.
 */
trait StreamUtils
{
    /**
     * Returns a stream over the specified string.
     *
     * @param string $code
     * @return StreamInterface
     */
    private function given_a_source_stream($code)
    {
        return Stream::factory($code);
    }

    /**
     * Returns a stream over the specified file.
     *
     * @param string $filename
     * @return StreamInterface
     */
    private function given_a_source_file($filename)
    {
        return Stream::factory(fopen($filename, 'r'));
    }
}
