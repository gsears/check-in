<?php

namespace App\Tests\Unit\Containers;

use App\Containers\SurveyQuestionResponseRisk;
use App\Entity\SurveyQuestionResponseInterface;
use PHPUnit\Framework\TestCase;

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
    public function testValidRiskLevels(int $riskLevel)
    {
        $surveyQuestionResponseMock = $this->createMock(SurveyQuestionResponseInterface::class);
        new SurveyQuestionResponseRisk($riskLevel, $surveyQuestionResponseMock);
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
    public function testInvalidRiskLevels(int $riskLevel)
    {
        $this->expectException(\InvalidArgumentException::class);
        $surveyQuestionResponseMock = $this->createMock(SurveyQuestionResponseInterface::class);
        new SurveyQuestionResponseRisk($riskLevel, $surveyQuestionResponseMock);
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
        $testInstance = new SurveyQuestionResponseRisk($riskLevel, $surveyQuestionResponseMock);
        $this->assertEquals($expectedWeightedRiskLevel, $testInstance->getWeightedRiskLevel());
    }
}
