<?php

/*
LabResponseRisk.php
Gareth Sears - 2493194S
*/

namespace App\Containers;

use App\Entity\LabResponse;

/**
 * A container class for wrapping labresponse risk queries from the question repositories.
 */
class LabResponseRisk
{
    const LEVEL_NONE = 0;
    const LEVEL_WARNING = 1;
    const LEVEL_DANGER = 2;

    const WEIGHT_NONE = 0;
    const WEIGHT_WARNING = 1;
    const WEIGHT_DANGER = 3;

    public static function getRiskLevels(): array
    {
        return [self::LEVEL_NONE, self::LEVEL_WARNING, self::LEVEL_DANGER];
    }

    private $questionRiskLevels = [];
    private $labResponse;

    public function __construct($questionRiskLevels, LabResponse $labResponse)
    {
        $this->questionRiskLevels = $questionRiskLevels;
        $this->labResponse = $labResponse;
    }

    /**
     * Return the risk factor as a percentage.
     *
     * @return void
     */
    public function getRiskFactor()
    {
        $maxRisk = count($this->questionRiskLevels) * self::WEIGHT_DANGER;
        return ceil(array_sum($this->getWeightedRisks()) / $maxRisk) * 100;
    }

    public function getWeightedRisks(bool $excludeZeroValues = true)
    {
        $weightedRisks = array_map(function ($riskLevel) {
            return [self::WEIGHT_NONE, self::WEIGHT_WARNING, self::WEIGHT_DANGER][$riskLevel];
        }, $this->questionRiskLevels);

        if ($excludeZeroValues) {
            $weightedRisks = array_filter($weightedRisks, function ($weightedRisk) {
                return $weightedRisk > 0;
            });
        }

        return $weightedRisks;
    }

    public function getLabResponse()
    {
        return $this->labResponse;
    }
}
