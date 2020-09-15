<?php

namespace Tests\Utils;

use Polygen\Language\Lexing\Matching\MatcherInput;
use Polygen\Language\Lexing\Position;
use Webmozart\Assert\Assert;

class MatcherInputHelper implements MatcherInput
{
    /**
     * @var string
     */
    private $input;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var int
     */
    private $inputLength;

    /**
     * @param string $input
     * @return MatcherInput
     */
    public static function get($input)
    {
        return new static($input);
    }

    /**
     * @param string $input
     */
    private function __construct($input)
    {
        Assert::string($input);
        $this->input = $input;
        $this->inputLength = strlen($input);
    }

    public function peek($chars = 1)
    {
        Assert::integer($chars);
        if ($this->isInputOver()) {
            return null;
        }
        return $this->justRead($chars);
    }

    public function read($chars = 1)
    {
        Assert::integer($chars);
        if ($this->isInputOver()) {
            return null;
        }
        $read = $this->justRead($chars);
        $this->position += $chars;
        return $read;
    }

    public function getPosition()
    {
        return new Position(
            // Count the number of newlines to determine the line we are on, add one since we want lines to start at 1.
            substr_count($this->input, PHP_EOL, 0, $this->getClampedPosition()) + 1,
            // Get the number of characters from the last newline we already parsed, up to the current cursor.
            $this->getClampedPosition() - strrpos(substr($this->input, 0, $this->getClampedPosition()), PHP_EOL)
        );
    }

    private function getClampedPosition()
    {
        return min($this->inputLength, $this->position);
    }

    /**
     * @return bool
     */
    private function isInputOver()
    {
        return $this->position > $this->inputLength;
    }

    /**
     * @param int $chars
     * @return false|string
     */
    private function justRead($chars)
    {
        return substr($this->input, $this->getClampedPosition(), $chars);
    }
}
