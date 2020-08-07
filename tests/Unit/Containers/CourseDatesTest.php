<?php

/*
CourseDatesTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Containers;

use App\Containers\CourseDates;
use PHPUnit\Framework\TestCase;

class CourseDatesTest extends TestCase
{
    public function validDateProvider()
    {
        yield ["20 November 2020", "21 November 2020"];
        yield ["20 November 2020", "20 December 2020"];
        yield ["5 August 2020", "20 December 2021"];
    }

    /**
     * @dataProvider validDateProvider
     */
    public function testValidDates(string $lowDateString, string $highDateString)
    {
        new CourseDates(date_create($lowDateString), date_create($highDateString));
        $this->assertTrue(true);
    }

    public function invalidDateProvider()
    {
        yield ["21 November 2020", "20 November 2020"];
        yield ["21 November 2020", "21 November 2020"];
        yield ["21 December 2020", "21 November 2020"];
        yield ["21 November 2021", "21 November 2020"];
    }

    /**
     * @dataProvider invalidDateProvider
     */
    public function testInvalidDates(string $lowDateString, string $highDateString)
    {
        $this->expectException(\InvalidArgumentException::class);
        new CourseDates(date_create($lowDateString), date_create($highDateString));
    }
}
