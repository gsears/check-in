<?php

/*
LabResponseRisk.php
Gareth Sears - 2493194S
*/

namespace App\Containers;

use App\Entity\LabResponse;

/**
 * A container class for wrapping labresponse risk queries from the question repositories.
 */
class LabResponseRisk
{
    public static function sortByWeightedRiskFactor(array $labResponseRisks)
    {
        uasort($labResponseRisks, function (LabResponseRisk $a, LabResponseRisk $b) {
            $riskFactorA = $a->getWeightedRiskFactor();
            $riskFactorB = $b->getWeightedRiskFactor();

            if ($riskFactorA > $riskFactorB) {
                return -1;
            } else if ($riskFactorA < $riskFactorB) {
                return 1;
            } else {
                return 0;
            }
        });
    }

    private $surveyQuestionResponseRisks = [];
    private $labResponse;

    /**
     * Creates a wrapper containing all question response risk objects for a lab survey.
     *
     * @param SurveyQuestionResponseRisk[] $surveyQuestionResponseRisks
     * @param LabResponse $labResponse
     */
    public function __construct(array $surveyQuestionResponseRisks, LabResponse $labResponse)
    {
        $this->surveyQuestionResponseRisks = $surveyQuestionResponseRisks;
        $this->labResponse = $labResponse;
    }

    /**
     * Returns the weighted risk factor, which is the % of maximum possible risk for this lab.
     *
     * For example, if a student is in a danger zone for every lab question, their factor is 100%.
     * If a student is in a danger zone for 1 / 2 questions, their risk factor is 50%.
     *
     * @return float The risk factor for this lab as a %.
     */
    public function getWeightedRiskFactor(): float
    {
        $questionCount = $this->labResponse->getLab()->getQuestionCount();
        $maxRisk = $questionCount * SurveyQuestionResponseRisk::WEIGHT_DANGER;

        return (array_sum($this->getWeightedRiskLevels()) / $maxRisk) * 100.0;
    }

    /**
     * Returns the SurveyQuestionResponseRisk objects for this lab response.
     *
     * @param boolean $excludeWithRiskLevelNone If true, any objects with a risk level of 0 will not be returned.
     * @return SurveyQuestionResponseRisks[]
     */
    public function getSurveyQuestionResponseRisks(bool $excludeWithRiskLevelNone = true): array
    {
        if (!$excludeWithRiskLevelNone) {
            return $this->surveyQuestionResponseRisks;
        }

        // array_values resets keys to count from 0
        return array_values(array_filter($this->surveyQuestionResponseRisks, function (SurveyQuestionResponseRisk $sqrr) {
            return $sqrr->getRiskLevel() !== SurveyQuestionResponseRisk::LEVEL_NONE;
        }));
    }

    /**
     * Returns the weighted risks of each question response in the lab survey in an array.
     *
     * @param boolean $excludeWithRiskLevelNone - If true, any objects with a risk level of 0 will not be returned.
     * @return float[]
     */
    public function getWeightedRiskLevels(bool $excludeWithRiskLevelNone = true): array
    {
        return array_map(function (SurveyQuestionResponseRisk $sqrr) {
            return $sqrr->getWeightedRiskLevel();
        }, $this->getSurveyQuestionResponseRisks($excludeWithRiskLevelNone));
    }

    /**
     * Returns the lab response associated with this risk wrapper.
     *
     * @return LabResponse
     */
    public function getLabResponse(): LabResponse
    {
        return $this->labResponse;
    }
}
