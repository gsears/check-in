<?php

/*
LabResponseRepositoryTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional\Repository;

use App\Containers\SurveyQuestionResponseRisk;
use App\Containers\XYCoordinates;
use App\Entity\LabResponse;
use App\Entity\SentimentQuestion;
use App\Tests\Functional\FunctionalTestCase;
use DateTime;

class LabResponseRepositoryTest extends FunctionalTestCase
{
    public function testFindOneByLabAndStudentMatch()
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

        /**
         * @var LabResponseRepository
         */
        $repo = $this->getEntityManager()->getRepository(LabResponse::class);
        $this->assertEquals($labResponse, $repo->findOneByLabAndStudent($lab, $student));
    }

    public function testFindOneByLabAndStudentNoMatch()
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

        /**
         * @var LabResponseRepository
         */
        $repo = $this->getEntityManager()->getRepository(LabResponse::class);
        $this->assertNull($repo->findOneByLabAndStudent($lab, $student));
    }

    public function testFindCompletedByCourseInstanceAndStudent()
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
            new DateTime("22 November 2020")
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

        $labOne = $creator->createLab(
            'testLab',
            new DateTime("20 November 2020"),
            $courseInstance
        );

        $labOneResponse = $creator->createLabResponse(
            true,
            $student,
            $labOne
        );

        $labTwo = $creator->createLab(
            'testLab2',
            new DateTime("21 November 2020"),
            $courseInstance
        );

        $labTwoResponse = $creator->createLabResponse(
            true,
            $student,
            $labTwo
        );

        $labThree = $creator->createLab(
            'testLab3',
            new DateTime("22 November 2020"),
            $courseInstance
        );

        $labThreeResponse = $creator->createLabResponse(
            false,
            $student,
            $labThree
        );

        /**
         * @var LabResponseRepository
         */
        $repo = $this->getEntityManager()->getRepository(LabResponse::class);

        $this->assertEquals([
            $labTwoResponse,
            $labOneResponse
        ], $repo->findCompletedByCourseInstanceAndStudent($courseInstance, $student));
    }

    public function testGetLabResponseRisk()
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
            SurveyQuestionResponseRisk::LEVEL_DANGER,
            0,
            5,
            0,
            5,
            $labXYQuestion
        );

        $labXYQuestionResponse = $creator->createLabXYQuestionResponse(
            new XYCoordinates(0, 0),
            $labXYQuestion,
            $labResponse
        );

        $sentimentQuestion = $creator->createSentimentQuestion(
            'testSentimentQuestion',
            'test'
        );

        $labSentimentQuestion = $creator->createLabSentimentQuestion(
            1,
            $sentimentQuestion,
            $lab
        );

        $labSentimentQuestionDangerZone = $creator->createLabSentimentQuestionDangerZone(
            SurveyQuestionResponseRisk::LEVEL_DANGER,
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

        // Refresh the lab and lab response objects to account for responses etc.
        $this->getEntityManager()->refresh($lab);
        $this->getEntityManager()->refresh($labResponse);

        /**
         * @var LabResponseRepository
         */
        $repo = $this->getEntityManager()->getRepository(LabResponse::class);
        $labResponseRisk = $repo->getLabResponseRisk($labResponse);

        $this->assertNotNull($labResponseRisk);

        // Check that the labResponseRisk contains the correct surveyResponseQuestions
        $surveyQuestionResponsesInLabResponseRisk = array_map(function (SurveyQuestionResponseRisk $surveyQuestionResponseRisk) {
            return $surveyQuestionResponseRisk->getSurveyQuestionResponse();
        }, $labResponseRisk->getSurveyQuestionResponseRisks());

        $this->assertEquals(2, count($surveyQuestionResponsesInLabResponseRisk));
        $this->assertContains($labXYQuestionResponse, $surveyQuestionResponsesInLabResponseRisk);
        $this->assertContains($labSentimentQuestionResponse, $surveyQuestionResponsesInLabResponseRisk);

        // Check that the weighted risk factor is as expected (100% for 2 danger responses)
        $this->assertEquals(100.0, $labResponseRisk->getWeightedRiskFactor());
    }
}
