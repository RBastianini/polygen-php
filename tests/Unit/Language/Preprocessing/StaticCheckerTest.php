<?php

namespace Tests\Polygen\Unit\Language\Preprocessing;

use Hamcrest\Matchers;
use Polygen\Document;
use Polygen\Language\Errors\Error;
use Polygen\Language\Errors\ErrorCollection;
use Polygen\Language\Preprocessing\StaticCheck\StaticCheckInterface;
use Polygen\Language\Preprocessing\StaticChecker;
use Tests\TestCase;

class StaticCheckerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_errors_if_there_are_some()
    {
        $error1 = $this->given_a_unique_error();
        $error2 = $this->given_a_unique_error();
        $error3 = $this->given_a_unique_error();

        $subject = new StaticChecker(
            [
                $this->given_a_static_check([$error1]),
                $this->given_a_static_check([]),
                $this->given_a_static_check([$error2, $error3]),
            ]
        );

        $result = $subject->check($this->given_a_document());
        $this->assertInstanceOf(ErrorCollection::class, $result);

        $this->assertEquals([$error1, $error2, $error3], $result->getErrors());
    }

    /**
     * @test
     */
    public function it_does_not_return_errors_if_there_isnt_any()
    {
        $subject = new StaticChecker(
            [
                $this->given_a_static_check([]),
                $this->given_a_static_check([]),
                $this->given_a_static_check([]),
            ]
        );

        $result = $subject->check($this->given_a_document());
        $this->assertInstanceOf(ErrorCollection::class, $result);

        $this->assertEmpty($result->getErrors());
    }

    /**
     * @param Error[] $errors
     */
    private function given_a_static_check(array $errors)
    {
        return \Mockery::mock(StaticCheckInterface::class)->shouldReceive('check')
            ->once()
            ->with(Matchers::anInstanceOf(Document::class))
            ->andReturn(new ErrorCollection($errors))
            ->getMock();
    }

    /**
     * @return Error
     */
    private function given_a_unique_error()
    {
        return \Mockery::mock(
            Error::class,
            ['getMessage' => uniqid()]
        );
    }

    /**
     * @return Document
     */
    private function given_a_document()
    {
        return \Mockery::mock(Document::class);
    }
}
