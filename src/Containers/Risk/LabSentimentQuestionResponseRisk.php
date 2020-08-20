<?php

namespace App\Containers\Risk;

class LabSentimentQuestionResponseRisk extends SurveyQuestionResponseRisk
{
    public function getTwigTemplate(): string
    {
        return 'risk_summary/sentiment_response.html.twig';
    }

    public function getContext(): array
    {
        /**
         * @var LabSentimentQuestionResponse
         */
        $surveyQuestionResponse = $this->getSurveyQuestionResponse();

        return [
            'text' => $surveyQuestionResponse->getText(),
            'classification' => $surveyQuestionResponse->getClassification(),
            'confidence' =>  $surveyQuestionResponse->getConfidence(),
        ];
    }
}
