<?php

namespace App\Entity;

use DateTime;
use InvalidArgumentException;

class CourseDates
{
    private $startDate;
    private $endDate;

    public function __construct(DateTime $startDate, DateTime $endDate)
    {

        if ($startDate > $endDate) {
            throw new InvalidArgumentException("Start date must be before end date", 1);
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
