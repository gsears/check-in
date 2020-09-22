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
    public function testGetLabResponse()
    {
        $labResponseMock = $this->createMock(LabResponse::class);
        $labResponseRisk = new LabResponseRisk([], $labResponseMock);
        $this->assertSame($labResponseMock, $labResponseRisk->getLabResponse());
    }

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

    public function testGetSurveyQuestionResponseRisksOnlyWithRisk()
    {
        $labResponseMock = $this->createMock(LabResponse::class);

        $sqrr1 = $this->createMock(SurveyQuestionResponseRisk::class);
        $sqrr1->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_NONE);

        $sqrr2 = $this->createMock(SurveyQuestionResponseRisk::class);
        $sqrr2->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_WARNING);

        $sqrr3 = $this->createMock(SurveyQuestionResponseRisk::class);
        $sqrr3->method('getRiskLevel')->willReturn(SurveyQuestionResponseRisk::LEVEL_DANGER);

        // Wrong order
        $labResponseRisk = new LabResponseRisk([$sqrr1, $sqrr2, $sqrr3], $labResponseMock);

        $this->assertEquals([$sqrr2, $sqrr3], $labResponseRisk->getSurveyQuestionResponseRisks(true));
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

    public function testSortByWeightedRiskFactor()
    {
        $labResponseRiskOrderOne = $this->createMock(LabResponseRisk::class);
        $labResponseRiskOrderOne->method('getWeightedRiskFactor')->willReturn(1.0);
        $labResponseRiskOrderTwo = $this->createMock(LabResponseRisk::class);
        $labResponseRiskOrderTwo->method('getWeightedRiskFactor')->willReturn(0.6);
        $labResponseRiskOrderThree = $this->createMock(LabResponseRisk::class);
        $labResponseRiskOrderThree->method('getWeightedRiskFactor')->willReturn(0.3);
        $labResponseRiskOrderFour = $this->createMock(LabResponseRisk::class);
        $labResponseRiskOrderFour->method('getWeightedRiskFactor')->willReturn(0.0);

        $testArray = [
            $labResponseRiskOrderFour,
            $labResponseRiskOrderTwo,
            $labResponseRiskOrderThree,
            $labResponseRiskOrderOne
        ];

        LabResponseRisk::sortByWeightedRiskFactor($testArray);

        $this->assertEquals([
            $labResponseRiskOrderOne,
            $labResponseRiskOrderTwo,
            $labResponseRiskOrderThree,
            $labResponseRiskOrderFour
        ], $testArray);
    }
}
