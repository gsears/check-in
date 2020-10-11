<?php

/*
SurveyQuestionResponseRepositoryInterface.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use App\Entity\SurveyQuestionResponseInterface;
use App\Containers\Risk\SurveyQuestionResponseRisk;

/**
 * An interface which requires survey question response implementations to return risk
 * wrapper objects.
 */
interface SurveyQuestionResponseRepositoryInterface
{
    /**
     * Fetches the risk thresholds from a lab and then calculates the level of risk
     * assigned to the response.
     *
     * @param SurveyQuestionResponseInterface $question
     * @return int The risk weight assigned to the response
     */
    public function getSurveyQuestionResponseRisk(SurveyQuestionResponseInterface $question): SurveyQuestionResponseRisk;
}
