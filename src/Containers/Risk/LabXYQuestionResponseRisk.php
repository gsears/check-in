<?php

namespace App\Containers\Risk;

use App\Containers\Risk\SurveyQuestionResponseRisk;

class LabXYQuestionResponseRisk extends SurveyQuestionResponseRisk
{
    public function getTwigTemplate(): string
    {
        return 'risk_summary/xy_response.html.twig';
    }

    public function getContext(): array
    {
        /**
         * @var LabXYQuestionResponse
         */
        $surveyQuestionResponse = $this->getSurveyQuestionResponse();

        /**
         * @var XYQuestion
         */
        $question = $surveyQuestionResponse->getSurveyQuestion()->getQuestion();
        $xAffectiveField = $question->getXField();
        $yAffectiveField = $question->getYField();

        return [
            'xFieldName' => $xAffectiveField->getName(),
            'xResponseValue' => $surveyQuestionResponse->getXValue(),
            'yFieldName' => $yAffectiveField->getName(),
            'yResponseValue' =>  $surveyQuestionResponse->getYValue(),
        ];
    }
}
