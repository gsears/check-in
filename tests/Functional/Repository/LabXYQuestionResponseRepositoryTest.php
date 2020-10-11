<?php

/*
LabXYQuestionResponseRepositoryTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional\Repository;

use DateTime;
use App\Containers\XYCoordinates;
use App\Entity\LabXYQuestionResponse;
use App\Tests\Functional\FunctionalTestCase;
use App\Containers\Risk\SurveyQuestionResponseRisk;

/**
 * Tests database query methods
 */
class LabXYQuestionResponseRepositoryTest extends FunctionalTestCase
{

    public function testGetSurveyQuestionResponseRiskNotInDangerZoneBounds()
    {
        $creator = $this->getEntityCreator();

        $course = $creator->createCourse(
            'testCourse',
            'test',
            null
        );

        $courseInstance = $creator->createCourseInstance(
            $course,
            new DateTime("20 November 2020"),
            new DateTime("21 November 2020")
        );

        $student = $creator->createStudent(
            'test',
            'test',
            '1234'
        );

        $enrolment = $creator->createEnrolment(
            $student,
            $courseInstance
        );

        $lab = $creator->createLab(
            'testLab',
            new DateTime("20 November 2020"),
            $courseInstance
        );

        $labResponse = $creator->createLabResponse(
            true,
            $student,
            $lab
        );

        $affectiveFieldA = $creator->createAffectiveField('test', 'low', 'high');
        $affectiveFieldB = $creator->createAffectiveField('test', 'low', 'high');

        $xyQuestion = $creator->createXYQuestion(
            'testXYQuestion',
            'test',
            $affectiveFieldA,
            $affectiveFieldB
        );

        $labXYQuestion = $creator->createLabXYQuestion(
            0,
            $xyQuestion,
            $lab
        );

        $labXYQuestionResponse = $creator->createLabXYQuestionResponse(
            new XYCoordinates(0, 0),
            $labXYQuestion,
            $labResponse
        );

        /**
         * @var LabXYQuestionResponseRepository
         */
        $repo = $this->getEntityManager()->getRepository(LabXYQuestionResponse::class);
        $surveyQuestionResponseRisk = $repo->getSurveyQuestionResponseRisk($labXYQuestionResponse);

        $this->assertNotNull($surveyQuestionResponseRisk);
        $this->assertEquals(SurveyQuestionResponseRisk::LEVEL_NONE, $surveyQuestionResponseRisk->getRiskLevel());
        $this->assertEquals($labXYQuestionResponse, $surveyQuestionResponseRisk->getSurveyQuestionResponse());
    }

    public function dangerZoneLevelProvider()
    {
        yield [SurveyQuestionResponseRisk::LEVEL_WARNING];
        yield [SurveyQuestionResponseRisk::LEVEL_DANGER];
    }

    /**
     * @dataProvider dangerZoneLevelProvider
     */
    public function testGetSurveyQuestionResponseRiskInWarningBounds(int $riskLevel)
    {
        $creator = $this->getEntityCreator();

        $course = $creator->createCourse(
            'testCourse',
            'test',
            null
        );

        $courseInstance = $creator->createCourseInstance(
            $course,
            new DateTime("20 November 2020"),
            new DateTime("21 November 2020")
        );

        $student = $creator->createStudent(
            'test',
            'test',
            '1234'
        );

        $enrolment = $creator->createEnrolment(
            $student,
            $courseInstance
        );

        $lab = $creator->createLab(
            'testLab',
            new DateTime("20 November 2020"),
            $courseInstance
        );

        $labResponse = $creator->createLabResponse(
            true,
            $student,
            $lab
        );

        $affectiveFieldA = $creator->createAffectiveField('test', 'low', 'high');
        $affectiveFieldB = $creator->createAffectiveField('test', 'low', 'high');

        $xyQuestion = $creator->createXYQuestion(
            'testXYQuestion',
            'test',
            $affectiveFieldA,
            $affectiveFieldB
        );

        $labXYQuestion = $creator->createLabXYQuestion(
            0,
            $xyQuestion,
            $lab
        );

        $labXYQuestionDangerZone = $creator->createLabXYQuestionDangerZone(
            $riskLevel,
            0,
            5,
            0,
            5,
            $labXYQuestion
        );

        $labXYQuestionResponse = $creator->createLabXYQuestionResponse(
            new XYCoordinates(4, 4),
            $labXYQuestion,
            $labResponse
        );

        /**
         * @var LabXYQuestionResponseRepository
         */
        $repo = $this->getEntityManager()->getRepository(LabXYQuestionResponse::class);
        $surveyQuestionResponseRisk = $repo->getSurveyQuestionResponseRisk($labXYQuestionResponse);

        $this->assertNotNull($surveyQuestionResponseRisk);
        $this->assertEquals($riskLevel, $surveyQuestionResponseRisk->getRiskLevel());
        $this->assertEquals($labXYQuestionResponse, $surveyQuestionResponseRisk->getSurveyQuestionResponse());
    }
}
