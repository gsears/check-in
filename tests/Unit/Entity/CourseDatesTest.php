<?php

namespace App\Tests\Unit\Entity;

use App\Entity\CourseDates;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CourseDatesTest extends TestCase
{

    public function dateProvider()
    {
        yield 'Start date cannot be before end date' => [
            '20 November 2020',
            '19 November 2020',
            false
        ];
        yield 'Valid dates' => [
            '19 November 2020',
            '20 November 2020',
            true
        ];
    }

    /**
     * @dataProvider dateProvider
     */
    public function testValidDates(string $startDate, string $endDate, $expectedPass)
    {
        if (!$expectedPass) {
            $this->expectException(InvalidArgumentException::class);
        }

        $courseDates = new CourseDates(date_create($startDate), date_create($endDate));

        $this->assertTrue(true);
    }
}
