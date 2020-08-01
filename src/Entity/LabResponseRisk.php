<?php

namespace App\Entity;

class LabResponseRisk
{
    const NONE = 0;
    const WARNING = 1;
    const DANGER = 3;

    private $questionRiskLevels = [];
    private $student;

    public function __construct($questionRiskLevels, $student)
    {
        $this->questionRiskLevels = $questionRiskLevels;
        $this->student = $student;
    }

    /**
     * Return the risk factor as a percentage.
     *
     * @return void
     */
    public function getRiskFactor()
    {
        $maxRisk = count($this->questionRiskLevels) * self::DANGER;
        return ceil(array_sum($this->questionRiskLevels) / $maxRisk) * 100;
    }

    public function getWeightedRisks(bool $excludeZeroValues = true)
    {
        $weightedRisks = array_map(function ($riskLevel) {
            return [self::NONE, self::WARNING, self::DANGER][$riskLevel];
        }, $this->questionRiskLevels);

        if ($excludeZeroValues) {
            $weightedRisks = array_filter($weightedRisks, function ($weightedRisk) {
                return $weightedRisk > 0;
            });
        }

        return $weightedRisks;
    }

    public function getStudent()
    {
        return $this->student;
    }
}
