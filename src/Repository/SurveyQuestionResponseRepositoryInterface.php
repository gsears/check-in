<?php

namespace App\Repository;

use App\Entity\SurveyQuestionResponseInterface;
use App\Containers\Risk\SurveyQuestionResponseRisk;

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
