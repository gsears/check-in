<?php

namespace App\Repository;

use App\Containers\SurveyQuestionResponseRisk;
use App\Entity\Lab;
use App\Entity\SurveyQuestionResponseInterface;

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
