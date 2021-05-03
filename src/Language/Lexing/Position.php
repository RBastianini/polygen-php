<?php

namespace Polygen\Language\Lexing;

use Webmozart\Assert\Assert;

/**
 * Represents a position in the file stream with line / column coordinates.
 */
class Position
{
    /**
     * @var int
     */
    private $line;

    /**
     * @var int
     */
    private $column;

    /**
     * @param int $line
     * @param int $column
     */
    public function __construct($line, $column)
    {
        Assert::integer($line);
        Assert::integer($column);
        Assert::greaterThan($line, 0);
        Assert::greaterThan($column, 0);

        $this->line = $line;
        $this->column = $column;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
    }


}
