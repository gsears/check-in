<?php

/*
SurveyQuestionResponseRisk.php
Gareth Sears - 2493194S
*/

namespace App\Containers\Risk;

use App\Entity\SurveyQuestionResponseInterface;

/**
 * This is an abstract class which provides a container for managing the risk
 * associated with a survey question response. Specifically, it stores the
 * risk level associated with that question response, provides helpers so that
 * the original response can be accessed, and also provides a default context
 * for a Twig view which can render the risk information (to be implemented by
 * subclasses, essentially following the abstract template method pattern).
 */
abstract class SurveyQuestionResponseRisk implements RiskInterface
{
    // Enums for risk levels
    const LEVEL_NONE = 0;
    const LEVEL_WARNING = 1;
    const LEVEL_DANGER = 2;

    // Risk weights for the above levels
    const WEIGHT_NONE = 0.0;
    const WEIGHT_WARNING = 1.0;
    const WEIGHT_DANGER = 3.0;

    const WEIGHT_DICT = [
        self::LEVEL_NONE => self::WEIGHT_NONE,
        self::LEVEL_WARNING => self::WEIGHT_WARNING,
        self::LEVEL_DANGER => self::WEIGHT_DANGER
    ];

    // Textual representations of the risk level (for views)
    const TEXT_NONE = 'None';
    const TEXT_WARNING = "Warning";
    const TEXT_DANGER = "Danger";

    const TEXT_DICT = [
        self::LEVEL_NONE => self::TEXT_NONE,
        self::LEVEL_WARNING => self::TEXT_WARNING,
        self::LEVEL_DANGER => self::TEXT_DANGER
    ];

    /**
     * A helper method to check if the risk level input (usually from a database query)
     * is valid.
     *
     * @param integer $riskLevel
     * @return boolean
     */
    public static function isValidRiskLevel(int $riskLevel): bool
    {
        return in_array($riskLevel, [self::LEVEL_NONE, self::LEVEL_WARNING, self::LEVEL_DANGER]);
    }

    private $riskLevel;
    private $surveyQuestionResponse;

    /**
     * Construct the object from the riskLevel (to be found via database query check against danger zones)
     * and the associated question response.
     *
     * @param integer $riskLevel
     * @param SurveyQuestionResponseInterface $surveyQuestionResponse
     */
    public function __construct(int $riskLevel, SurveyQuestionResponseInterface $surveyQuestionResponse)
    {
        if (!self::isValidRiskLevel($riskLevel)) {
            throw new \InvalidArgumentException($riskLevel . " is not a valid risk level.");
        }
        $this->riskLevel = $riskLevel;
        $this->surveyQuestionResponse = $surveyQuestionResponse;
    }

    /**
     * Returns the risk level associated with the survey question response.
     *
     * @return integer
     */
    public function getRiskLevel(): int
    {
        return $this->riskLevel;
    }

    /**
     * Returns the risk level associated with the survey question response in textual form
     *
     * @return string
     */
    public function getRiskText(): string
    {
        return self::TEXT_DICT[$this->riskLevel];
    }

    /**
     * Returns the weighted risk level associated with the survey question response.
     *
     * @return float
     */
    public function getWeightedRiskLevel(): float
    {
        return self::WEIGHT_DICT[$this->riskLevel];
    }

    /**
     * Returns the survey question response that was assigned the risk value.
     *
     * @return SurveyQuestionResponseInterface
     */
    public function getSurveyQuestionResponse(): SurveyQuestionResponseInterface
    {
        return $this->surveyQuestionResponse;
    }

    /**
     * Returns the index of the response's survey question.
     *
     * @return integer
     */
    public function getQuestionIndex(): int
    {
        return $this->surveyQuestionResponse->getSurveyQuestion()->getIndex();
    }

    /**
     * Returns the default context expected from all SurveyQuestionResponseRisk subclasses
     * for use in a Twig view template.
     *
     * This is used in the twig function App\Twig\AppExtension::renderRisk, which essentially
     * mixes in this with the specific context of any subclasses.
     *
     * @return array
     */
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

    /**
     * Returns a context dictionary of data which can be used by the Twig view template.
     *
     * @return array
     */
    public function getContext(): array
    {
        return [];
    }

    /**
     * Returns the path of the Twig template associated with this SurveyQuestionResponseRisk.
     *
     * This is used in the twig function App\Twig\AppExtension::renderRisk, which calls a
     * specific template depending on the response type.
     *
     * This pattern very much follows how Symfony renders forms with its Twig form() function.
     *
     * @return string
     */
    abstract public function getTwigTemplate(): string;
}
