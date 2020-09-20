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
class CourseSummaryPageControllerTest extends FunctionalTestCase
{
    private $courseInstance;

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
    }

    public function testAnonymousUserRedirected()
    {
        $client = static::createClient();
        $client->request('GET', '/courses/CS101/1');
        $this->assertResponseRedirects('/login', 302, "Anonymous user is redirected");
    }

    public function testNonMemberStudentDenied()
    {
        $creator = $this->getEntityCreator();

        $testNonMemberStudent = $creator->createStudent(
            'testFirstname',
            'testSurname',
            '2345678'
        );

        $client = static::createClient();
        $client->loginUser($testNonMemberStudent->getUser());
        $client->request('GET', '/courses/CS101/1');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testMemberStudentDenied()
    {
        $creator = $this->getEntityCreator();

        $testMemberStudent = $creator->createStudent(
            'testFirstname',
            'testSurname',
            '1234567'
        );

        $creator->createEnrolment(
            $testMemberStudent,
            $this->courseInstance
        );

        $client = static::createClient();
        $client->loginUser($testMemberStudent->getUser());
        $client->request('GET', '/courses/CS101/1');
        $this->assertResponseStatusCodeSame(403);
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
        $client->request('GET', '/courses/CS101/1');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testMemberInstructorAccessGranted()
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
        $client->request('GET', '/courses/CS101/1');
        $this->assertResponseIsSuccessful();
    }
}
