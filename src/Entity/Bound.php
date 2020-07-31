<?php

namespace App\Entity;

use InvalidArgumentException;

class Bound
{
    private $lowBound;
    private $highBound;

    public function __construct(int $low, int $high)
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
