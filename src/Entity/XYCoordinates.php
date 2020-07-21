<?php

namespace App\Entity;

use InvalidArgumentException;

class XYCoordinates
{
    private $x;
    private $y;

    public function __construct(int $x, int $y)
    {
        if (!$x || !$y) {
            throw new InvalidArgumentException("X and Y must exist!", 1);
        }

        $this->x = $x;
        $this->y = $y;
    }

    public function getX()
    {
        return $this->x;
    }

    public function getY()
    {
        return $this->y;
    }
}
