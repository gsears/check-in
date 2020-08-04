<?php

namespace App\Containers;

/*
CourseInstanceRisk.php
Gareth Sears - 2493194S
*/

use App\Containers\LabResponseRisk;
use App\Entity\Enrolment;

class EnrolmentRisk
{
    public static function sortByAverageRisk($enrolmentRisks)
    {
        uasort($enrolmentRisks, function ($a, $b) {
            $riskFactorA = $a->getAverageRiskFactor();
            $riskFactorB = $b->getAverageRiskFactor();

            if ($riskFactorA > $riskFactorB) {
                return 1;
            } else if ($riskFactorA < $riskFactorB) {
                return -1;
            } else {
                return 0;
            }
        });
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

    public function getAverageRiskFactor(): int
    {
        $risks = array_map(function (LabResponseRisk $labResponseRisk) {
            return $labResponseRisk->getRiskFactor();
        }, $this->labResponseRisks);

        if (count($risks) === 0) {
            return 0;
        }

        return array_sum($risks) / count($risks);
    }

    public function areAllRisksAbove(int $riskFactor): bool
    {
        foreach ($this->labResponseRisks as $labResponseRisk) {
            if ($labResponseRisk->getRiskFactor() < $riskFactor) {
                return false;
            }
        }

        return true;
    }

    public function flagStudent()
    {
        $this->enrolment->setRiskFlag(Enrolment::FLAG_AUTOMATIC);
    }
}
