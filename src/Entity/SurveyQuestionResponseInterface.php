<?php

namespace App\Entity;

/**
 * An interface to help typecheck question types
 */
interface SurveyQuestionResponseInterface
{
    public function getSurveyQuestion(): SurveyQuestionInterface;
}
