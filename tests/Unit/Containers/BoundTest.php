<?php

/*
BoundTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Containers;

use App\Containers\Bound;
use PHPUnit\Framework\TestCase;

class BoundTest extends TestCase
{
    public function validBoundProvider()
    {
        yield [1, 2];
        yield [-2, 3];
        yield [-5, -3];
        yield [0, 0];
        yield [1, 1];
        yield [-1, -1];
    }

    /**
     * @dataProvider validBoundProvider
     */
    public function testValidBounds(int $lowBound, int $highBound)
    {
        new Bound($lowBound, $highBound);
        $this->assertTrue(true);
    }

    public function invalidBoundProvider()
    {
        yield [5, 4];
        yield [2, -1];
        yield [-1, -3];
    }

    /**
     * @dataProvider invalidBoundProvider
     */
    public function testInvalidBounds(int $lowBound, int $highBound)
    {
        $this->expectException(\InvalidArgumentException::class);
        new Bound($lowBound, $highBound);
    }
}
