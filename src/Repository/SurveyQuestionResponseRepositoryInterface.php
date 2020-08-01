<?php

namespace App\Repository;

use App\Entity\Lab;
use App\Entity\SurveyQuestionResponseInterface;

interface SurveyQuestionResponseRepositoryInterface
{
    /**
     * Fetches the risk thresholds from a lab and then calculates the level of risk
     * assigned to the response.
     *
     * @param SurveyQuestionResponseInterface $question
     * @param Lab $lab
     * @return int The risk weight assigned to the response
     */
    public function getRiskScore(SurveyQuestionResponseInterface $question): int;
}
