<?php

namespace App\Entity;

class Risk
{
    const NONE = 0;
    const WARNING = 1;
    const DANGER = 3;

    static public function getWeightedRisk(int $riskLevel)
    {
        return [self::NONE, self::WARNING, self::DANGER][$riskLevel];
    }
}
