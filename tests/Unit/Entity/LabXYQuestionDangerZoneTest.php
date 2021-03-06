<?php

/*
LabXYQuestionDangerZoneTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\LabXYQuestionDangerZone;
use App\Containers\Risk\SurveyQuestionResponseRisk;

/**
 * Tests to ensure methods for LabXYQuestionDangerZone
 * have checks to ensure valid state in database.
 */
class LabXYQuestionDangerZoneTest extends TestCase
{
    public function riskLevelProvider()
    {
        yield [SurveyQuestionResponseRisk::LEVEL_NONE];
        yield [SurveyQuestionResponseRisk::LEVEL_WARNING];
        yield [SurveyQuestionResponseRisk::LEVEL_DANGER];
    }

    /**
     * @dataProvider riskLevelProvider
     */
    public function testValidRiskLevels($riskLevel)
    {
        $labXYQuestionDangerZone = new LabXYQuestionDangerZone();
        $labXYQuestionDangerZone->setRiskLevel($riskLevel);
        $this->assertTrue(true);
    }

    public function invalidRiskLevelProvider()
    {
        yield [-1];
        yield [100];
        yield [3];
    }

    /**
     * @dataProvider invalidRiskLevelProvider
     */
    public function testInvalidRiskLevels($riskLevel)
    {
        $this->expectException(\InvalidArgumentException::class);
        $labXYQuestionDangerZone = new LabXYQuestionDangerZone();
        $labXYQuestionDangerZone->setRiskLevel($riskLevel);
        $this->fail();
    }
}
