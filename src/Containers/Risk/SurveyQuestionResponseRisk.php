<?php

namespace App\Containers\Risk;

use App\Entity\SurveyQuestionInterface;
use App\Entity\SurveyQuestionResponseInterface;

abstract class SurveyQuestionResponseRisk implements RiskInterface
{
    const LEVEL_NONE = 0;
    const LEVEL_WARNING = 1;
    const LEVEL_DANGER = 2;

    const TEXT_NONE = 'None';
    const TEXT_WARNING = "Warning";
    const TEXT_DANGER = "Danger";

    const WEIGHT_NONE = 0.0;
    const WEIGHT_WARNING = 1.0;
    const WEIGHT_DANGER = 3.0;

    public static function isValidRiskLevel(int $riskLevel): bool
    {
        return in_array($riskLevel, [self::LEVEL_NONE, self::LEVEL_WARNING, self::LEVEL_DANGER]);
    }

    private $riskLevel;
    private $surveyQuestionResponse;
    private $html;

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

    public function getRiskText(): string
    {
        return [
            self::TEXT_NONE,
            self::TEXT_WARNING,
            self::TEXT_DANGER
        ][$this->riskLevel];
    }

    public function getWeightedRiskLevel(): float
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

    public function getQuestionIndex(): int
    {
        return $this->surveyQuestionResponse->getSurveyQuestion()->getIndex();
    }

    final public function getDefaultContext(): array
    {
        $surveyQuestion =  $this->surveyQuestionResponse->getSurveyQuestion();
        $question = $surveyQuestion->getQuestion();
        return [
            "questionIndex" => $surveyQuestion->getIndex(),
            "questionName" => $question->getName(),
            "questionText" => $question->getQuestionText(),
            "riskLevel" => $this->getRiskLevel(),
            "riskText" => $this->getRiskText(),
            "weightedRiskLevel" => $this->getWeightedRiskLevel()
        ];
    }

    public function getContext(): array
    {
        return [];
    }

    abstract public function getTwigTemplate(): string;
}
