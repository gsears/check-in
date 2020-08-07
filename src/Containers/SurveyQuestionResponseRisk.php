<?php

namespace App\Containers;

use App\Entity\SurveyQuestionResponseInterface;

class SurveyQuestionResponseRisk
{
    const LEVEL_NONE = 0;
    const LEVEL_WARNING = 1;
    const LEVEL_DANGER = 2;

    const WEIGHT_NONE = 0.0;
    const WEIGHT_WARNING = 1.0;
    const WEIGHT_DANGER = 3.0;

    public static function isValidRiskLevel(int $riskLevel): bool
    {
        return in_array($riskLevel, [self::LEVEL_NONE, self::LEVEL_WARNING, self::LEVEL_DANGER]);
    }

    private $riskLevel;
    private $surveyQuestionResponse;

    public function __construct(int $riskLevel, SurveyQuestionResponseInterface $surveyQuestionResponse)
    {
        if (!self::isValidRiskLevel($riskLevel)) {
            throw new \InvalidArgumentException($riskLevel . " is not a valid risk level.");
        }
        $this->riskLevel = $riskLevel;
        $this->surveyQuestionResponse = $surveyQuestionResponse;
    }

    public function getRiskLevel(): int
    {
        return $this->riskLevel;
    }

    public function getWeightedRiskLevel(): int
    {
        return [
            self::WEIGHT_NONE,
            self::WEIGHT_WARNING,
            self::WEIGHT_DANGER
        ][$this->riskLevel];
    }

    public function getSurveyQuestionResponse(): SurveyQuestionResponseInterface
    {
        return $this->surveyQuestionResponse;
    }
}
