<?php

/*
CourseSummaryPageTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional\Controller\CourseController;

use App\Provider\DateTimeProvider;
use App\Tests\Functional\FunctionalTestCase;

class CourseSummaryPageTest extends FunctionalTestCase
{

    private $nonMemberStudentUser;
    private $memberStudentUser;
    private $nonMemberInstructorUser;
    private $memberInstructorUser;

    private $startDate;
    private $endDate;

    /**
     * Create database data for testing with. This is rolled back after each test.
     */
    protected function setUp()
    {
        $creator = $this->getEntityCreator();

        $dateTimeProvider = new DateTimeProvider();
        $courseStart =  ($dateTimeProvider->getCurrentDateTime()->modify("- 1 week"));
        $this->startDate = $courseStart->format("d/m/Y");
        $courseEnd = ($dateTimeProvider->getCurrentDateTime()->modify("+ 1 week"));
        $this->endDate = $courseEnd->format("d/m/Y");

        $testCourseOne = $creator->createCourse(
            'CS101',
            'Programming',
            null
        );

        $testCourseInstanceOne = $creator->createCourseInstance(
            $testCourseOne,
            $courseStart,
            $courseEnd
        );

        $testCourseInstanceOne
            ->setIndexInCourse(1);

        $testMemberStudent = $creator->createStudent(
            'testFirstname',
            'testSurname',
            '1234567'
        );

        $this->memberStudentUser = $testMemberStudent->getUser();

        $testNonMemberStudent = $creator->createStudent(
            'testFirstname',
            'testSurname',
            '2345678'
        );

        $this->nonMemberStudentUser = $testNonMemberStudent->getUser();

        $creator->createEnrolment(
            $testMemberStudent,
            $testCourseInstanceOne
        );

        $testMemberInstructor = $creator->createInstructor(
            'testFirstname',
            'testSecondName',
            '1@test.com'
        );

        $this->memberInstructorUser = $testMemberInstructor->getUser();

        $testCourseInstanceOne->addInstructor($testMemberInstructor);

        $testNonMemberInstructor = $creator->createInstructor(
            'testFirstname',
            'testSecondName',
            '2@test.com'
        );

        $this->nonMemberInstructorUser = $testNonMemberInstructor->getUser();
    }

    public function testAnonymousUserRedirected()
    {
        $client = static::createClient();
        $client->request('GET', '/courses/CS101/1');
        $this->assertResponseRedirects('/login', 302, "Anonymous user is redirected");
    }

    public function testNonMemberStudentDenied()
    {
        $client = static::createClient();
        $client->loginUser($this->nonMemberStudentUser);
        $client->request('GET', '/courses/CS101/1');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testMemberStudentDenied()
    {
        $client = static::createClient();
        $client->loginUser($this->memberStudentUser);
        $client->request('GET', '/courses/CS101/1');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testNonMemberInstructorDenied()
    {
        $client = static::createClient();
        $client->loginUser($this->nonMemberInstructorUser);
        $client->request('GET', '/courses/CS101/1');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testMemberInstructorAccessGranted()
    {
        $client = static::createClient();
        $client->loginUser($this->memberInstructorUser);
        $client->request('GET', '/courses/CS101/1');
        $this->assertResponseIsSuccessful();
    }

    public function testTitleAndHeaders()
    {
        $client = static::createClient();
        $client->loginUser($this->memberInstructorUser);
        $crawler = $client->request('GET', '/courses/CS101/1');
        $this->assertPageTitleContains("Course Summary for CS101");
        $this->assertSelectorTextContains('html header > h1', 'CS101 - Programming', 'Title is not correct.');
        $this->assertSelectorTextContains('html header > p', sprintf("%s - %s", $this->startDate, $this->endDate));
    }
}
