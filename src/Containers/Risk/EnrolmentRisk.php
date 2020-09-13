<?php

namespace App\Containers\Risk;

/*
CourseInstanceRisk.php
Gareth Sears - 2493194S
*/

use App\Entity\Enrolment;

/**
 * A container class for wrapping all lab response risks for a particular enrolment.
 * The functions in this class are those primarily used for automatic risk detection
 * on a course, such as 'average risk' and 'all risks above', etc.
 */
class EnrolmentRisk
{
    /**
     * @deprecated In favour of client sorting.
     *
     * Helper function to sort EnrolmentRisk objects in risk order,
     * highest risk first.
     *
     * @param EnrolmentRisk[] $enrolmentRisks
     * @return EnrolmentRisk[]
     */
    public static function sortByAverageRisk($enrolmentRisks): array
    {
        // Sorts in place, but array is not passed by reference so need to return local variable.
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

    /**
     * @param LabResponseRisk[] $labResponseRisks
     * @param Enrolment $enrolment
     */
    public function __construct($labResponseRisks, Enrolment $enrolment)
    {
        $this->labResponseRisks = $labResponseRisks;
        $this->enrolment = $enrolment;
    }

    /**
     * Return the enrolment associated with this container.
     *
     * @return Enrolment
     */
    public function getEnrolment(): Enrolment
    {
        return $this->enrolment;
    }

    /**
     * Return the lab response risk objects associated with this container.
     *
     * @return LabResponseRisk[]
     */
    public function getLabResponseRisks(): array
    {
        return $this->labResponseRisks;
    }

    /**
     * Returns the average risk factor for all the lab responses associated with this wrapper.
     *
     * @return float The average risk
     */
    public function getAverageRiskFactor(): float
    {
        // Early return for no risks.
        if (count($this->labResponseRisks) === 0) {
            return 0.0;
        }

        $risks = array_map(function (LabResponseRisk $labResponseRisk) {
            return $labResponseRisk->getWeightedRiskFactor();
        }, $this->labResponseRisks);

        return array_sum($risks) / (float) count($risks);
    }

    /**
     * Returns true if the student is 'at risk' for this course.
     * Calculated as all risks being above the course instance threshold.
     *
     * @return boolean True if the student is at risk.
     */
    public function isAtRisk(): bool
    {
        return $this->areAllRisksAbove($this->enrolment->getCourseInstance()->getRiskThreshold());
    }

    /**
     * Returns true if all the lab response risks associated with this wrapper have a risk
     * factor above the argument provided.
     *
     * @param float $riskFactor
     * @return boolean True if all lab response risks are above the risk factor provided.
     */
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
