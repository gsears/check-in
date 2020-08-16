<?php

/*
XYCoordinates.php
Gareth Sears - 2493194S
*/

namespace App\Containers;

use InvalidArgumentException;

/**
 * A wrapper class for XY Coordinates
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

    public function getX()
    {
        return $this->x;
    }

    public function getY()
    {
        return $this->y;
    }
}
