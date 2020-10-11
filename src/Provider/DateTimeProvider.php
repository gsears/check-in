<?php

/*
DateTimeProvider.php
Gareth Sears - 2493194S
*/

namespace App\Provider;

use App\DataFixtures\EvaluationFixtures;
use DateTime;

/**
 * This is used to provide the app's current date and time.
 * It allows mocking the in app time for testing and evaluation.
 */
class DateTimeProvider
{
    public function getCurrentDateTime(): DateTime
    {
        // return new DateTime();
        return date_create(EvaluationFixtures::SIMULATED_CURRENT_DATE);
    }
}
