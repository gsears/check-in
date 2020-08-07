<?php

namespace App\Tests\Unit\Containers;

use App\Containers\LabResponseRisk;
use App\Containers\SurveyQuestionResponseRisk;
use App\Entity\Lab;
use App\Entity\LabResponse;
use PHPUnit\Framework\TestCase;

class LabResponseRiskTest extends TestCase
{

    public function testSurveyQuestionResponseRisks()
    {
        $labResponseMock = $this->createMock(LabResponse::class);

        $questionResponseMock1 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock1->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_NONE);

        $questionResponseMock2 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock2->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_WARNING);

        $testInstance = new LabResponseRisk([$questionResponseMock1, $questionResponseMock2], $labResponseMock);

        $this->assertEquals([
            $questionResponseMock1,
            $questionResponseMock2
        ], $testInstance->getSurveyQuestionResponseRisks(false));
    }

    public function testSurveyQuestionResponseRisksExcludeNone()
    {
        $labResponseMock = $this->createMock(LabResponse::class);

        $questionResponseMock1 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock1->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_NONE);

        $questionResponseMock2 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock2->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_WARNING);

        $testInstance = new LabResponseRisk([$questionResponseMock1, $questionResponseMock2], $labResponseMock);

        $this->assertEquals([
            $questionResponseMock2
        ], $testInstance->getSurveyQuestionResponseRisks(true));
    }

    public function testGetWeightedRiskLevels()
    {
        $labResponseMock = $this->createMock(LabResponse::class);

        $questionResponseMock1 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock1->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_NONE);
        $questionResponseMock1->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_NONE);

        $questionResponseMock2 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock2->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_WARNING);
        $questionResponseMock2->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_WARNING);

        $questionResponseMock3 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock3->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_DANGER);
        $questionResponseMock3->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_DANGER);

        $testInstance = new LabResponseRisk([$questionResponseMock1, $questionResponseMock2, $questionResponseMock3], $labResponseMock);

        $this->assertEquals([
            SurveyQuestionResponseRisk::WEIGHT_NONE,
            SurveyQuestionResponseRisk::WEIGHT_WARNING,
            SurveyQuestionResponseRisk::WEIGHT_DANGER
        ], $testInstance->getWeightedRiskLevels(false));
    }


    public function testGetWeightedRiskLevelsExcludeNone()
    {
        $labResponseMock = $this->createMock(LabResponse::class);

        $questionResponseMock1 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock1->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_NONE);
        $questionResponseMock1->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_NONE);

        $questionResponseMock2 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock2->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_WARNING);
        $questionResponseMock2->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_WARNING);

        $questionResponseMock3 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock3->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_DANGER);
        $questionResponseMock3->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_DANGER);

        $testInstance = new LabResponseRisk([$questionResponseMock1, $questionResponseMock2, $questionResponseMock3], $labResponseMock);

        $this->assertEquals([
            SurveyQuestionResponseRisk::WEIGHT_WARNING,
            SurveyQuestionResponseRisk::WEIGHT_DANGER
        ], $testInstance->getWeightedRiskLevels(true));
    }

    public function testGetWeightedRiskFactor()
    {
        $labMock = $this->createMock(Lab::class);
        $labMock->method('getQuestionCount')->willReturn(4);

        $labResponseMock = $this->createMock(LabResponse::class);
        $labResponseMock->method('getLab')->willReturn($labMock);

        // Create 4 question responses.
        $questionResponseMock1 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock1->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_NONE);
        $questionResponseMock1->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_NONE);

        $questionResponseMock2 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock2->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_WARNING);
        $questionResponseMock2->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_WARNING);

        $questionResponseMock3 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock3->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_DANGER);
        $questionResponseMock3->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_DANGER);

        $questionResponseMock4 = $this->createMock(SurveyQuestionResponseRisk::class);
        $questionResponseMock4->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_DANGER);
        $questionResponseMock4->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_DANGER);

        $testInstance = new LabResponseRisk([$questionResponseMock1, $questionResponseMock2, $questionResponseMock3, $questionResponseMock4], $labResponseMock);

        // Should return percentage
        $maxPossibleWeight = SurveyQuestionResponseRisk::WEIGHT_DANGER +
            SurveyQuestionResponseRisk::WEIGHT_DANGER +
            SurveyQuestionResponseRisk::WEIGHT_DANGER +
            SurveyQuestionResponseRisk::WEIGHT_DANGER;

        $actualWeight = SurveyQuestionResponseRisk::WEIGHT_NONE +
            SurveyQuestionResponseRisk::WEIGHT_WARNING +
            SurveyQuestionResponseRisk::WEIGHT_DANGER +
            SurveyQuestionResponseRisk::WEIGHT_DANGER;

        $expectedPercentage = ($actualWeight / $maxPossibleWeight) * 100.0;

        $this->assertEquals($expectedPercentage, $testInstance->getWeightedRiskFactor());
    }
}
