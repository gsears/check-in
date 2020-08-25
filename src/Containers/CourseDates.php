<?php

/*
CourseDates.php
Gareth Sears - 2493194S
*/

namespace App\Containers;

use DateTime;
use InvalidArgumentException;

class CourseDates
{
    private $startDate;
    private $endDate;

    public function __construct(DateTime $startDate, DateTime $endDate)
    {

        // == because we don't want to check for object equality, just value.
        if ($startDate > $endDate || $startDate == $endDate) {
            throw new InvalidArgumentException("Start date must be at least 1 day before end date", 1);
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }
}
