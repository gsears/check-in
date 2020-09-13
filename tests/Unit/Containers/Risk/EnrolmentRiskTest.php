<?php

/*
EnrolmentRiskTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Containers\Risk;

use App\Entity\Enrolment;
use PHPUnit\Framework\TestCase;
use App\Containers\Risk\EnrolmentRisk;
use App\Containers\Risk\LabResponseRisk;

class EnrolmentRiskTest extends TestCase
{
    public function averageRiskFactorProvider()
    {
        yield [[1, 1, 1], 1.0];

        yield [[0, 1, 0, 1], 0.5];

        yield [[], 0.0];

        yield [[1, 2, 3, 4], 2.5];
    }

    /**
     * @dataProvider averageRiskFactorProvider
     */
    public function testGetAverageRiskFactor(array $weightedRiskFactors, float $expectedResult)
    {
        $mockLabResponseRisks = $this->createLabResponseRiskMocks($weightedRiskFactors);
        $mockEnrolment = $this->createMock(Enrolment::class);

        $enrolmentRisk = new EnrolmentRisk($mockLabResponseRisks, $mockEnrolment);

        $this->assertEquals($expectedResult, $enrolmentRisk->getAverageRiskFactor());
    }

    public function allRisksAboveTrueProvider()
    {
        yield [[1.0, 0.2, 0.7], 0.1];
        yield [[1.7, 3.5, 2.5], 1.2];
        yield [[0.1, 0.1, 0.1], 0.1];
        yield [[0.0, 0.0, 0.0], 0.0];
        yield [[-1.0, -0.2, 0.7], -1.1];
    }

    /**
     * @dataProvider allRisksAboveTrueProvider
     */
    public function testAreAllRisksAboveTrue(array $weightedRiskFactors, float $areAbove)
    {
        $mockLabResponseRisks = $this->createLabResponseRiskMocks($weightedRiskFactors);
        $mockEnrolment = $this->createMock(Enrolment::class);

        $enrolmentRisk = new EnrolmentRisk($mockLabResponseRisks, $mockEnrolment);

        $this->assertTrue($enrolmentRisk->areAllRisksAbove($areAbove));
    }

    public function allRisksAboveFalseProvider()
    {
        yield [[1.0, 0.2, 0.7], 0.21];
        yield [[1.7, 3.5, 2.5], 4.0];
        yield [[-0.1, -0.1, 0.1], 0.1];
    }

    /**
     * @dataProvider allRisksAboveFalseProvider
     */
    public function testAreAllRisksAboveFalse(array $weightedRiskFactors, float $areAbove)
    {
        $mockLabResponseRisks = $this->createLabResponseRiskMocks($weightedRiskFactors);
        $mockEnrolment = $this->createMock(Enrolment::class);

        $enrolmentRisk = new EnrolmentRisk($mockLabResponseRisks, $mockEnrolment);

        $this->assertFalse($enrolmentRisk->areAllRisksAbove($areAbove));
    }

    public function testSortByAverageRisk()
    {
        $weightedRiskFactorsOne = [0.1, 0.1, 0.1];
        $mockLabResponseRisksOne = $this->createLabResponseRiskMocks($weightedRiskFactorsOne);
        $mockEnrolmentOne = $this->createMock(Enrolment::class);

        $enrolmentRiskOrderFour = new EnrolmentRisk($mockLabResponseRisksOne, $mockEnrolmentOne);

        $weightedRiskFactorsTwo = [0.2, 0.2, 0.2];
        $mockLabResponseRisksTwo = $this->createLabResponseRiskMocks($weightedRiskFactorsTwo);
        $mockEnrolmentTwo = $this->createMock(Enrolment::class);

        $enrolmentRiskOrderTwo = new EnrolmentRisk($mockLabResponseRisksTwo, $mockEnrolmentTwo);

        $weightedRiskFactorsThree = [0.3, 0.3, 0.3];
        $mockLabResponseRisksThree = $this->createLabResponseRiskMocks($weightedRiskFactorsThree);
        $mockEnrolmentThree = $this->createMock(Enrolment::class);

        $enrolmentRiskOrderOne = new EnrolmentRisk($mockLabResponseRisksThree, $mockEnrolmentThree);

        $weightedRiskFactorsFour = [0.1, 0.2, 0.1];
        $mockLabResponseRisksFour = $this->createLabResponseRiskMocks($weightedRiskFactorsFour);
        $mockEnrolmentFour = $this->createMock(Enrolment::class);

        $enrolmentRiskOrderThree = new EnrolmentRisk($mockLabResponseRisksFour, $mockEnrolmentFour);

        $testArray = [
            $enrolmentRiskOrderFour,
            $enrolmentRiskOrderTwo,
            $enrolmentRiskOrderOne,
            $enrolmentRiskOrderThree
        ];

        EnrolmentRisk::sortByAverageRisk($testArray);

        $this->assertEquals([
            $enrolmentRiskOrderOne,
            $enrolmentRiskOrderTwo,
            $enrolmentRiskOrderThree,
            $enrolmentRiskOrderFour
        ], $testArray);
    }

    private function createLabResponseRiskMocks(array $weightedRiskFactorsToReturn)
    {
        return array_map(function ($riskFactor) {
            $mockLabResponseRisk = $this->createMock(LabResponseRisk::class);
            $mockLabResponseRisk->method('getWeightedRiskFactor')->willReturn($riskFactor);
            return $mockLabResponseRisk;
        }, $weightedRiskFactorsToReturn);
    }
}
