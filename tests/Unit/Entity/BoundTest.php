<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Bound;
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

        $bound = new Bound($lowBound, $highBound);

        $this->assertTrue(true);
    }
}
