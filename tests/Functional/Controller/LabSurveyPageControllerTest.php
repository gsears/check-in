<?php

/*
CourseSummaryPageTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional\Controller;

use App\Provider\DateTimeProvider;
use App\Tests\Functional\FunctionalTestCase;

/**
 * Tests security / user access for a course summary page.
 */
class LabSurveyPageControllerTest extends FunctionalTestCase
{
    private $courseInstance;
    private $student;
    private $response;

    /**
     * Creates the course instance under test.
     */
    protected function setUp()
    {
        $creator = $this->getEntityCreator();

        $dateTimeProvider = new DateTimeProvider();
        $courseStart =  ($dateTimeProvider->getCurrentDateTime()->modify("- 1 week"));
        $courseEnd = ($dateTimeProvider->getCurrentDateTime()->modify("+ 1 week"));

        $testCourseOne = $creator->createCourse(
            'CS101',
            'Programming',
            null
        );

        $courseInstance = $creator->createCourseInstance(
            $testCourseOne,
            $courseStart,
            $courseEnd
        );

        $courseInstance->setIndexInCourse(1);

        $this->courseInstance = $courseInstance;

        $student = $creator->createStudent(
            'studentName',
            'studentSurname',
            '1234567'
        );

        $this->student = $student;

        $creator->createEnrolment(
            $student,
            $courseInstance
        );

        $lab = $creator->createLab(
            'Lab One',
            $courseStart,
            $courseInstance
        );

        $response = $creator->createLabResponse(
            false, // Initially response not submitted
            $student,
            $lab
        );

        $this->response = $response;

        // Create some lab questions
        $testQuestion = $creator->createSentimentQuestion(
            'Test Question',
            'Text'
        );

        $q1 = $creator->createLabSentimentQuestion(
            0,
            $testQuestion,
            $lab
        );

        $q2 = $creator->createLabSentimentQuestion(
            1,
            $testQuestion,
            $lab
        );


        $q3 = $creator->createLabSentimentQuestion(
            2,
            $testQuestion,
            $lab
        );
    }

    public function testAnonymousUserRedirected()
    {
        $client = static::createClient();
        $client->request('GET', '/courses/CS101/1/lab/lab-one/1234567/survey/1');
        $this->assertResponseRedirects('/login', 302, "Anonymous user is redirected");
    }

    public function testOtherStudentDenied()
    {
        $creator = $this->getEntityCreator();

        $testNonMemberStudent = $creator->createStudent(
            'testFirstname',
            'testSurname',
            '2345678'
        );

        $client = static::createClient();
        $client->loginUser($testNonMemberStudent->getUser());
        $client->request('GET', '/courses/CS101/1/lab/lab-one/1234567/survey/1');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testOwningStudentAccessGranted()
    {
        $client = static::createClient();
        $client->loginUser($this->student->getUser());
        $client->request('GET', '/courses/CS101/1/lab/lab-one/1234567/survey/1');
        $this->assertResponseIsSuccessful();
    }

    public function testNonMemberInstructorDenied()
    {
        $creator = $this->getEntityCreator();

        $testNonMemberInstructor = $creator->createInstructor(
            'testFirstname',
            'testSurname',
            '2@test.com'
        );

        $client = static::createClient();
        $client->loginUser($testNonMemberInstructor->getUser());
        $client->request('GET', '/courses/CS101/1/lab/lab-one/1234567/survey/1');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testCourseInstructorDenied()
    {
        $creator = $this->getEntityCreator();

        $testMemberInstructor = $creator->createInstructor(
            'testFirstname',
            'testSurname',
            '1@test.com'
        );

        $this->courseInstance->addInstructor($testMemberInstructor);
        // Commit change to db.
        $this->getEntityManager()->flush();

        $client = static::createClient();
        $client->loginUser($testMemberInstructor->getUser());
        $client->request('GET', '/courses/CS101/1/lab/lab-one/1234567/survey/1');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testCompletedSurveyDenied()
    {
        $this->response->setSubmitted(true);
        $this->getEntityManager()->flush();

        $client = static::createClient();
        $client->loginUser($this->student->getUser());
        $client->request('GET', '/courses/CS101/1/lab/lab-one/1234567/survey/1');
        $this->assertResponseStatusCodeSame(403);
    }

    public function pageProvider()
    {
        yield [2];
        yield [3];
    }

    /**
     * @dataProvider pageProvider
     */
    public function testSkipQuestionDenied(int $skippedPage)
    {
        $client = static::createClient();
        $client->loginUser($this->student->getUser());
        $client->request('GET', '/courses/CS101/1/lab/lab-one/1234567/survey/' . $skippedPage);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testSurveyPageDoesNotExist()
    {
        $client = static::createClient();
        $client->loginUser($this->student->getUser());
        $client->request('GET', '/courses/CS101/1/lab/lab-one/1234567/survey/4');
        $this->assertResponseStatusCodeSame(404);
    }
}
