<?php

/*
LabSentimentQuestionDangerZoneTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\LabSentimentQuestionDangerZone;
use App\Containers\Risk\SurveyQuestionResponseRisk;

/**
 * Tests to ensure methods for LabSentimentQuestionDangerZones
 * have checks to ensure valid state in database.
 */
class LabSentimentQuestionDangerZoneTest extends TestCase
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
        $labSentimentQuestionDangerZone = new LabSentimentQuestionDangerZone();
        $labSentimentQuestionDangerZone->setRiskLevel($riskLevel);
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
        $labSentimentQuestionDangerZone = new LabSentimentQuestionDangerZone();
        $labSentimentQuestionDangerZone->setRiskLevel($riskLevel);
        $this->fail();
    }
}
