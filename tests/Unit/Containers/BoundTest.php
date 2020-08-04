<?php

/*
BoundTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Containers;

use App\Containers\Bound;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BoundTest extends TestCase
{
    public function boundProvider()
    {
        yield 'High bound cannot be below low bound' => [
            5,
            4,
            false
        ];
        yield 'Valid bounds' => [
            4,
            5,
            true
        ];
    }

    /**
     * @dataProvider boundProvider
     */
    public function testValidBounds(int $lowBound, int $highBound, $expectedPass)
    {
        if (!$expectedPass) {
            $this->expectException(InvalidArgumentException::class);
        }

        new Bound($lowBound, $highBound);

        $this->assertTrue(true);
    }
}
