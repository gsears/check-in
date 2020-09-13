<?php

/*
LabResponseRisk.php
Gareth Sears - 2493194S
*/

namespace App\Containers\Risk;

use App\Entity\LabResponse;

/**
 * A container class for wrapping labresponse risk queries from the question repositories.
 */
class LabResponseRisk
{
    /**
     * @deprecated In favour of client sorting.
     *
     * Sorts an array of labResponseRisks in order of weighted risk.
     *
     * @param LabResponseRisk[] $labResponseRisks
     * @return LabResponseRisk[] An array of lab response risks sorted, highest risk first.
     */
    public static function sortByWeightedRiskFactor(array $labResponseRisks): array
    {
        // Sorts in place, but array is not pass by reference so need to return local variable.
        uasort($labResponseRisks, function (LabResponseRisk $a, LabResponseRisk $b) {
            return $a->getWeightedRiskFactor() >= $b->getWeightedRiskFactor() ? -1 : 1;
        });

        return $labResponseRisks;
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
        // Sort surveyQuestionResponseRisks into question order for retrieval.
        uasort($surveyQuestionResponseRisks, function (SurveyQuestionResponseRisk $a, SurveyQuestionResponseRisk $b) {
            return $a->getQuestionIndex() >= $b->getQuestionIndex() ? 1 : -1;
        });

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
    public function getSurveyQuestionResponseRisks(bool $excludeWithRiskLevelNone = false): array
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
     * Returns the lab response associated with this risk wrapper.
     *
     * @return LabResponse
     */
    public function getLabResponse(): LabResponse
    {
        return $this->labResponse;
    }

    private function getWeightedRiskLevels(): array
    {
        return array_map(function (SurveyQuestionResponseRisk $sqrr) {
            return $sqrr->getWeightedRiskLevel();
        }, $this->getSurveyQuestionResponseRisks(false));
    }
}
