<?php

namespace App\Provider;

use App\DataFixtures\EvaluationFixtures;
use DateTime;

class DateTimeProvider
{
    public function getCurrentDateTime(): DateTime
    {
        // return new DateTime();
        return date_create(EvaluationFixtures::SIMULATED_CURRENT_DATE);
    }
}
