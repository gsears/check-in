<?php

/*
LabSentimentQuestionDangerZoneTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Entity;

use App\Containers\SurveyQuestionResponseRisk;
use App\Entity\LabSentimentQuestionDangerZone;
use PHPUnit\Framework\TestCase;

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
