<?php

/*
XYCoordinates.php
Gareth Sears - 2493194S
*/

namespace App\Containers;

/**
 * A container class for storing XY coordinates for better type safety than
 * a dictionary.
 */
class XYCoordinates
{
    private $x;
    private $y;

    // Null checks not needed because of type hints
    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }
}
