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
            'xLowLabel' => $xAffectiveField->getLowLabel(),
            'xHighLabel' => $xAffectiveField->getHighLabel(),
            'xResponseValue' => $surveyQuestionResponse->getXValue(),
            'yFieldName' => $yAffectiveField->getName(),
            'yLowLabel' => $yAffectiveField->getLowLabel(),
            'yHighLabel' => $yAffectiveField->getHighLabel(),
            'yResponseValue' =>  $surveyQuestionResponse->getYValue(),
        ];
    }
}
