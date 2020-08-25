<?php

/*
LabSentimentQuestionResponseRepositoryTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional\Repository;

use DateTime;
use App\Entity\SentimentQuestion;
use App\Entity\LabXYQuestionResponse;
use App\Entity\LabSentimentQuestionResponse;
use App\Tests\Functional\FunctionalTestCase;
use App\Containers\Risk\SurveyQuestionResponseRisk;

class LabSentimentQuestionResponseRepositoryTest extends FunctionalTestCase
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

        $sentimentQuestion = $creator->createSentimentQuestion(
            'testSentimentQuestion',
            'test'
        );

        $labSentimentQuestion = $creator->createLabSentimentQuestion(
            0,
            $sentimentQuestion,
            $lab
        );

        $labSentimentQuestionResponse = $creator->createLabSentimentQuestionResponse(
            'test response',
            SentimentQuestion::NEUTRAL,
            0.9,
            $labSentimentQuestion,
            $labResponse
        );

        /**
         * @var LabXYQuestionResponseRepository
         */
        $repo = $this->getEntityManager()->getRepository(LabXYQuestionResponse::class);
        $surveyQuestionResponseRisk = $repo->getSurveyQuestionResponseRisk($labSentimentQuestionResponse);

        $this->assertNotNull($surveyQuestionResponseRisk);
        $this->assertEquals(SurveyQuestionResponseRisk::LEVEL_NONE, $surveyQuestionResponseRisk->getRiskLevel());
        $this->assertEquals($labSentimentQuestionResponse, $surveyQuestionResponseRisk->getSurveyQuestionResponse());
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

        $sentimentQuestion = $creator->createSentimentQuestion(
            'testSentimentQuestion',
            'test'
        );

        $labSentimentQuestion = $creator->createLabSentimentQuestion(
            0,
            $sentimentQuestion,
            $lab
        );

        $labSentimentQuestionDangerZone = $creator->createLabSentimentQuestionDangerZone(
            $riskLevel,
            SentimentQuestion::NEGATIVE,
            0.8,
            1.0,
            $labSentimentQuestion
        );

        $labSentimentQuestionResponse = $creator->createLabSentimentQuestionResponse(
            'test response',
            SentimentQuestion::NEGATIVE,
            0.9,
            $labSentimentQuestion,
            $labResponse
        );

        /**
         * @var LabSentimentQuestionResponseRepository
         */
        $repo = $this->getEntityManager()->getRepository(LabSentimentQuestionResponse::class);
        $surveyQuestionResponseRisk = $repo->getSurveyQuestionResponseRisk($labSentimentQuestionResponse);

        $this->assertNotNull($surveyQuestionResponseRisk);
        $this->assertEquals($riskLevel, $surveyQuestionResponseRisk->getRiskLevel());
        $this->assertEquals($labSentimentQuestionResponse, $surveyQuestionResponseRisk->getSurveyQuestionResponse());
    }
}
