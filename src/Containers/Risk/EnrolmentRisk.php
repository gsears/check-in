<?php

namespace App\Containers\Risk;

/*
CourseInstanceRisk.php
Gareth Sears - 2493194S
*/

use App\Entity\Enrolment;

class EnrolmentRisk
{
    public static function sortByAverageRisk($enrolmentRisks)
    {
        uasort($enrolmentRisks, function ($a, $b) {
            $riskFactorA = $a->getAverageRiskFactor();
            $riskFactorB = $b->getAverageRiskFactor();

            if ($riskFactorA < $riskFactorB) {
                return 1;
            } else if ($riskFactorA > $riskFactorB) {
                return -1;
            } else {
                return 0;
            }
        });

        return $enrolmentRisks;
    }

    private $labResponseRisks;
    private $enrolment;

    public function __construct($labResponseRisks, Enrolment $enrolment)
    {
        $this->labResponseRisks = $labResponseRisks;
        $this->enrolment = $enrolment;
    }

    public function getEnrolment(): Enrolment
    {
        return $this->enrolment;
    }

    public function getLabResponseRisks(): array
    {
        return $this->labResponseRisks;
    }

    public function getAverageRiskFactor(): float
    {
        if (count($this->labResponseRisks) === 0) {
            return 0.0;
        }

        $risks = array_map(function (LabResponseRisk $labResponseRisk) {
            return $labResponseRisk->getWeightedRiskFactor();
        }, $this->labResponseRisks);

        return array_sum($risks) / (float) count($risks);
    }

    public function isAtRisk(): bool
    {
        return $this->areAllRisksAbove($this->enrolment->getCourseInstance()->getRiskThreshold());
    }

    public function areAllRisksAbove(float $riskFactor): bool
    {
        foreach ($this->labResponseRisks as $labResponseRisk) {
            if ($labResponseRisk->getWeightedRiskFactor() < $riskFactor) {
                return false;
            }
        }

        return true;
    }
}
