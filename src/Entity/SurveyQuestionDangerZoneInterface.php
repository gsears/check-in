<?php

/*
SurveyQuestionDangerZoneInterface.php
Gareth Sears - 2493194S
*/

namespace App\Entity;

/**
 * An interface for survey questions which have a risk rating, and are used
 * for determining students at risk.
 */
interface SurveyQuestionDangerZoneInterface
{
    public function getRiskLevel(): ?int;
    public function setRiskLevel(int $riskLevel): self;
}
