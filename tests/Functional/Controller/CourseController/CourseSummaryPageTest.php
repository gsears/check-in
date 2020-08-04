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
        $this->startDate = $courseStart;
        $courseEnd = ($dateTimeProvider->getCurrentDateTime()->modify("+ 1 week"));
        $this->endDate = $courseEnd;

        $testCourseOne = $creator->createCourse(
            'CS101',
            'Programming',
            null
        );

        $testCourseInstance = $creator->createCourseInstance(
            $testCourseOne,
            $courseStart,
            $courseEnd
        );

        $testCourseInstance
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
            $testCourseInstance
        );

        $testMemberInstructor = $creator->createInstructor(
            'testFirstname',
            'testSurname',
            '1@test.com'
        );

        $this->memberInstructorUser = $testMemberInstructor->getUser();

        $testCourseInstance->addInstructor($testMemberInstructor);

        $testNonMemberInstructor = $creator->createInstructor(
            'testFirstname',
            'testSurname',
            '2@test.com'
        );

        $this->nonMemberInstructorUser = $testNonMemberInstructor->getUser();

        $testLabStarted = $creator->createLab(
            'Lab 1',
            $courseStart,
            $testCourseInstance
        );

        $testLabNotStarted = $creator->createLab(
            'Lab 2',
            $courseEnd,
            $testCourseInstance
        );
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
        $this->assertSelectorTextContains('html header > h1', 'CS101 - Programming');
        $this->assertSelectorTextContains('html header > p', sprintf("%s - %s", $this->startDate->format("d/m/Y"), $this->endDate->format("d/m/Y")));
    }

    public function testDisplayInstructorsAndEmail()
    {
        $client = static::createClient();
        $client->loginUser($this->memberInstructorUser);
        $crawler = $client->request('GET', '/courses/CS101/1');

        $this->assertSelectorTextContains('#instructor-card-list li', 'testFirstname testSurname');
        $messageLink = $crawler->filter('#instructor-card-list a')->extract(['_text', 'href'])[0];

        $this->assertEquals('Message', trim($messageLink[0]));
        $this->assertEquals('mailto:1@test.com', $messageLink[1]);
    }

    public function testDisplayLabs()
    {
        $client = static::createClient();
        $client->loginUser($this->memberInstructorUser);
        $crawler = $client->request('GET', '/courses/CS101/1');

        $tableText = $crawler->filter('#lab-table tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                return trim($td->text());
            });
        });

        // First column contains date
        $dateText = $tableText[0][0];
        $this->assertEquals($this->startDate->format('d/m/Y'), trim($dateText));

        // Second column contains name
        $nameText = $tableText[0][1];
        $this->assertEquals('Lab 1', trim($nameText));

        // Third column contains status (if before date: Open, else Inactive)
        $firstLabStatus = $tableText[0][2];
        $this->assertEquals('Open', trim($firstLabStatus));

        $secondLabStatus = $tableText[1][2];
        $this->assertEquals('Inactive', trim($secondLabStatus));

        // Fourth column contains view link
        $tableViewLinks = $crawler->filter('#lab-table tbody')->selectLink('View')->links();

        $this->assertStringContainsString('/courses/CS101/1/lab/lab-1', $tableViewLinks[0]->getUri());
        $this->assertStringContainsString('/courses/CS101/1/lab/lab-2', $tableViewLinks[1]->getUri());
    }
}
