<?php

namespace App\Provider;

use App\DataFixtures\AppFixtures;
use DateTime;

class DateTimeProvider
{
    public function getCurrentDateTime() : DateTime
    {
        // return new DateTime();
        return date_create(AppFixtures::SIMULATED_CURRENT_DATE);
    }
}
