<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface SurveyQuestionInterface
{

    public function getIndex(): int;

    public function setIndex(int $index): SurveyQuestionInterface;

    public function getQuestion(): QuestionInterface;

    /**
     * @return Collection|SurveyQuestionDangerZoneInterface[]
     */
    public function getDangerZones(): Collection;

    /**
     * @return Collection|SurveyQuestionResponseInterface
     */
    public function getResponses(): Collection;
}
