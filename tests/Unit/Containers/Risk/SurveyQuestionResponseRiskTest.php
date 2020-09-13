<?php

/*
SurveyQuestionResponseRiskTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Containers\Risk;

use PHPUnit\Framework\TestCase;
use App\Entity\SurveyQuestionResponseInterface;
use App\Containers\Risk\SurveyQuestionResponseRisk;

class SurveyQuestionResponseRiskTest extends TestCase
{
    public function validRiskLevelProvider()
    {
        yield [SurveyQuestionResponseRisk::LEVEL_NONE];
        yield [SurveyQuestionResponseRisk::LEVEL_WARNING];
        yield [SurveyQuestionResponseRisk::LEVEL_DANGER];
    }

    /**
     * @dataProvider validRiskLevelProvider
     */
    public function testConstructWithValidRiskLevels(int $riskLevel)
    {
        $surveyQuestionResponseMock = $this->createMock(SurveyQuestionResponseInterface::class);

        $surveyQuestionResponseRisk = $this->getMockForAbstractClass(
            SurveyQuestionResponseRisk::class,
            [$riskLevel, $surveyQuestionResponseMock]
        );

        $this->assertTrue(true);
    }

    public function invalidRiskLevelProvider()
    {
        yield [-1];
        yield [5];
        yield [3];
    }

    /**
     * @dataProvider invalidRiskLevelProvider
     */
    public function testConstructWithInvalidRiskLevels(int $riskLevel)
    {
        $this->expectException(\InvalidArgumentException::class);
        $surveyQuestionResponseMock = $this->createMock(SurveyQuestionResponseInterface::class);

        $surveyQuestionResponseRisk = $this->getMockForAbstractClass(
            SurveyQuestionResponseRisk::class,
            [$riskLevel, $surveyQuestionResponseMock]
        );
    }

    public function weightedRiskLevelProvider()
    {
        yield [
            "level" => SurveyQuestionResponseRisk::LEVEL_DANGER,
            "expected" => SurveyQuestionResponseRisk::WEIGHT_DANGER
        ];
        yield [
            "level" => SurveyQuestionResponseRisk::LEVEL_WARNING,
            "expected" => SurveyQuestionResponseRisk::WEIGHT_WARNING
        ];
        yield [
            "level" => SurveyQuestionResponseRisk::LEVEL_NONE,
            "expected" => SurveyQuestionResponseRisk::WEIGHT_NONE
        ];
    }

    /**
     * @dataProvider weightedRiskLevelProvider
     */
    public function testGetWeightedRiskLevel(int $riskLevel, int $expectedWeightedRiskLevel)
    {
        $surveyQuestionResponseMock = $this->createMock(SurveyQuestionResponseInterface::class);

        $surveyQuestionResponseRisk = $this->getMockForAbstractClass(
            SurveyQuestionResponseRisk::class,
            [$riskLevel, $surveyQuestionResponseMock]
        );

        $this->assertEquals($expectedWeightedRiskLevel, $surveyQuestionResponseRisk->getWeightedRiskLevel());
    }

    public function riskTextProvider()
    {
        yield [
            "level" => SurveyQuestionResponseRisk::LEVEL_DANGER,
            "expected" => SurveyQuestionResponseRisk::TEXT_DANGER
        ];
        yield [
            "level" => SurveyQuestionResponseRisk::LEVEL_WARNING,
            "expected" => SurveyQuestionResponseRisk::TEXT_WARNING
        ];
        yield [
            "level" => SurveyQuestionResponseRisk::LEVEL_NONE,
            "expected" => SurveyQuestionResponseRisk::TEXT_NONE
        ];
    }

    /**
     * @dataProvider riskTextProvider
     */
    public function testGetRiskText(int $riskLevel, string $expectedText)
    {
        $surveyQuestionResponseMock = $this->createMock(SurveyQuestionResponseInterface::class);

        $surveyQuestionResponseRisk = $this->getMockForAbstractClass(
            SurveyQuestionResponseRisk::class,
            [$riskLevel, $surveyQuestionResponseMock]
        );

        $this->assertEquals($expectedText, $surveyQuestionResponseRisk->getRiskText());
    }
}
