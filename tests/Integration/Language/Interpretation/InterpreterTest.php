<?php

namespace Tests\Integration\Language\Interpretation;

use Polygen\Language\Document;
use Polygen\Language\Interpretation\Context;
use Polygen\Polygen;
use Tests\StreamUtils;
use Tests\TestCase;

class InterpreterTest extends TestCase
{
    use StreamUtils;

    /**
     * @test
     */
    public function it_does_not_eat_up_all_available_stack_space()
    {
        $polygen = new Polygen();
        $document = $polygen->getDocument(
            $this->given_a_source_file(__DIR__ . '/../../../files/incredible-commit.grm'),
            Document::START
        );

        // Initialize a context with a seed known to make the interpreter use up all stack space and crash.
        $context = new Context(Document::START, '188900443');

        $result = $polygen->generate($document, $context);

        $this->assertNotEmpty($result);
    }
}
