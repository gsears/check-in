<?php

/*
Bound.php
Gareth Sears - 2493194S
*/

namespace App\Containers;

use InvalidArgumentException;

/**
 * A container class for storing and validating bounds (for XY components etc.)
 */
class Bound
{
    private $lowBound;
    private $highBound;

    /**
     *
     * @param int|float $low
     * @param int|float $high
     */
    public function __construct($low, $high)
    {
        if ($low > $high) {
            throw new InvalidArgumentException("Low bound must be < High bound", 1);
        }

        $this->lowBound = $low;
        $this->highBound = $high;
    }

    public function getLowBound()
    {
        return $this->lowBound;
    }

    public function getHighBound()
    {
        return $this->highBound;
    }
}
