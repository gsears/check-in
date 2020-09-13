<?php

/*
LabResponseRiskTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Containers\Risk;

use App\Entity\Lab;
use App\Entity\LabResponse;
use PHPUnit\Framework\TestCase;
use App\Containers\Risk\LabResponseRisk;
use App\Containers\Risk\SurveyQuestionResponseRisk;

class LabResponseRiskTest extends TestCase
{
    public function testWeightedRiskFactor()
    {
        $labMock = $this->createMock(Lab::class);
        $labMock->method('getQuestionCount')->willReturn(2);
        $labResponseMock = $this->createMock(LabResponse::class);
        $labResponseMock->method('getLab')->willReturn($labMock);

        // 3
        $sqrr1 = $this->createMock(SurveyQuestionResponseRisk::class);
        $sqrr1->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_DANGER);

        // 1
        $sqrr2 = $this->createMock(SurveyQuestionResponseRisk::class);
        $sqrr2->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_WARNING);

        $labResponseRisk = new LabResponseRisk([$sqrr1, $sqrr2], $labResponseMock);
        $this->assertEquals((4.0 / 6.0) * 100.0, $labResponseRisk->getWeightedRiskFactor());
    }

    public function testSurveyResponsesOrdered()
    {
        $labResponseMock = $this->createMock(LabResponse::class);

        $sqrr1 = $this->createMock(SurveyQuestionResponseRisk::class);
        $sqrr1->method('getQuestionIndex')->willReturn(1);

        $sqrr2 = $this->createMock(SurveyQuestionResponseRisk::class);
        $sqrr2->method('getQuestionIndex')->willReturn(2);

        // Wrong order
        $labResponseRisk = new LabResponseRisk([$sqrr2, $sqrr1], $labResponseMock);

        $this->assertEquals([$sqrr1, $sqrr2], $labResponseRisk->getSurveyQuestionResponseRisks());
    }

    // public function testSurveyQuestionResponseRisks()
    // {
    //     $labResponseMock = $this->createMock(LabResponse::class);

    //     $questionResponseMock1 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock1->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_NONE);

    //     $questionResponseMock2 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock2->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_WARNING);

    //     $testInstance = new LabResponseRisk([$questionResponseMock1, $questionResponseMock2], $labResponseMock);

    //     $this->assertEquals([
    //         $questionResponseMock1,
    //         $questionResponseMock2
    //     ], $testInstance->getSurveyQuestionResponseRisks(false));
    // }

    // public function testSurveyQuestionResponseRisksExcludeNone()
    // {
    //     $labResponseMock = $this->createMock(LabResponse::class);

    //     $questionResponseMock1 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock1->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_NONE);

    //     $questionResponseMock2 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock2->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_WARNING);

    //     $testInstance = new LabResponseRisk([$questionResponseMock1, $questionResponseMock2], $labResponseMock);

    //     $this->assertEquals([
    //         $questionResponseMock2
    //     ], $testInstance->getSurveyQuestionResponseRisks(true));
    // }

    // public function testGetWeightedRiskLevels()
    // {
    //     $labResponseMock = $this->createMock(LabResponse::class);

    //     $questionResponseMock1 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock1->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_NONE);
    //     $questionResponseMock1->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_NONE);

    //     $questionResponseMock2 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock2->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_WARNING);
    //     $questionResponseMock2->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_WARNING);

    //     $questionResponseMock3 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock3->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_DANGER);
    //     $questionResponseMock3->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_DANGER);

    //     $testInstance = new LabResponseRisk([$questionResponseMock1, $questionResponseMock2, $questionResponseMock3], $labResponseMock);

    //     $this->assertEquals([
    //         SurveyQuestionResponseRisk::WEIGHT_NONE,
    //         SurveyQuestionResponseRisk::WEIGHT_WARNING,
    //         SurveyQuestionResponseRisk::WEIGHT_DANGER
    //     ], $testInstance->getWeightedRiskLevels(false));
    // }


    // public function testGetWeightedRiskLevelsExcludeNone()
    // {
    //     $labResponseMock = $this->createMock(LabResponse::class);

    //     $questionResponseMock1 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock1->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_NONE);
    //     $questionResponseMock1->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_NONE);

    //     $questionResponseMock2 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock2->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_WARNING);
    //     $questionResponseMock2->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_WARNING);

    //     $questionResponseMock3 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock3->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_DANGER);
    //     $questionResponseMock3->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_DANGER);

    //     $testInstance = new LabResponseRisk([$questionResponseMock1, $questionResponseMock2, $questionResponseMock3], $labResponseMock);

    //     $this->assertEquals([
    //         SurveyQuestionResponseRisk::WEIGHT_WARNING,
    //         SurveyQuestionResponseRisk::WEIGHT_DANGER
    //     ], $testInstance->getWeightedRiskLevels(true));
    // }

    // public function testGetWeightedRiskFactor()
    // {
    //     $labMock = $this->createMock(Lab::class);
    //     $labMock->method('getQuestionCount')->willReturn(4);

    //     $labResponseMock = $this->createMock(LabResponse::class);
    //     $labResponseMock->method('getLab')->willReturn($labMock);

    //     // Create 4 question responses.
    //     $questionResponseMock1 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock1->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_NONE);
    //     $questionResponseMock1->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_NONE);

    //     $questionResponseMock2 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock2->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_WARNING);
    //     $questionResponseMock2->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_WARNING);

    //     $questionResponseMock3 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock3->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_DANGER);
    //     $questionResponseMock3->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_DANGER);

    //     $questionResponseMock4 = $this->createMock(SurveyQuestionResponseRisk::class);
    //     $questionResponseMock4->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_DANGER);
    //     $questionResponseMock4->method('getWeightedRiskLevel')->willReturn(SurveyQuestionResponseRisk::WEIGHT_DANGER);

    //     $testInstance = new LabResponseRisk([$questionResponseMock1, $questionResponseMock2, $questionResponseMock3, $questionResponseMock4], $labResponseMock);

    //     // Should return percentage
    //     $maxPossibleWeight = SurveyQuestionResponseRisk::WEIGHT_DANGER +
    //         SurveyQuestionResponseRisk::WEIGHT_DANGER +
    //         SurveyQuestionResponseRisk::WEIGHT_DANGER +
    //         SurveyQuestionResponseRisk::WEIGHT_DANGER;

    //     $actualWeight = SurveyQuestionResponseRisk::WEIGHT_NONE +
    //         SurveyQuestionResponseRisk::WEIGHT_WARNING +
    //         SurveyQuestionResponseRisk::WEIGHT_DANGER +
    //         SurveyQuestionResponseRisk::WEIGHT_DANGER;

    //     $expectedPercentage = ($actualWeight / $maxPossibleWeight) * 100.0;

    //     $this->assertEquals($expectedPercentage, $testInstance->getWeightedRiskFactor());
    // }

    // public function testSortByWeightedRiskFactor()
    // {
    //     $labResponseRiskOrderOne = $this->createMock(LabResponseRisk::class);
    //     $labResponseRiskOrderOne->method('getWeightedRiskFactor')->willReturn(1.0);
    //     $labResponseRiskOrderTwo = $this->createMock(LabResponseRisk::class);
    //     $labResponseRiskOrderTwo->method('getWeightedRiskFactor')->willReturn(0.6);
    //     $labResponseRiskOrderThree = $this->createMock(LabResponseRisk::class);
    //     $labResponseRiskOrderThree->method('getWeightedRiskFactor')->willReturn(0.3);
    //     $labResponseRiskOrderFour = $this->createMock(LabResponseRisk::class);
    //     $labResponseRiskOrderFour->method('getWeightedRiskFactor')->willReturn(0.0);

    //     $testArray = [
    //         $labResponseRiskOrderFour,
    //         $labResponseRiskOrderTwo,
    //         $labResponseRiskOrderThree,
    //         $labResponseRiskOrderOne
    //     ];

    //     LabResponseRisk::sortByWeightedRiskFactor($testArray);

    //     $this->assertEquals([
    //         $labResponseRiskOrderOne,
    //         $labResponseRiskOrderTwo,
    //         $labResponseRiskOrderThree,
    //         $labResponseRiskOrderFour
    //     ], $testArray);
    // }
}
