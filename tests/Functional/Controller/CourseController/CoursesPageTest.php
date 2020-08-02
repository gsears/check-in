<?php

/*
CoursesPageTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional\Controller\CourseController;

use App\Provider\DateTimeProvider;
use App\Tests\Functional\FunctionalTestCase;

class CoursesPageTest extends FunctionalTestCase
{
    private $instructorUser;
    private $studentUser;

    /**
     * Create database data for testing with. This is rolled back after each test.
     */
    protected function setUp()
    {
        $dateTimeProvider = new DateTimeProvider();
        $courseStart =  ($dateTimeProvider->getCurrentDateTime()->modify("- 1 week"));
        $courseEnd = ($dateTimeProvider->getCurrentDateTime()->modify("+ 1 week"));

        $creator = $this->getEntityCreator();

        $testInstructor = $creator->createInstructor(
            'instructorName',
            'instructorSurname',
        );

        $this->instructorUser = $testInstructor->getUser();

        $testStudent = $creator->createStudent(
            'studentName',
            'studentSurname',
            '1234567'
        );

        $this->studentUser = $testStudent->getUser();

        $testCourseOne = $creator->createCourse(
            'CS101',
            'Programming',
            null
        );

        $testCourseTwo = $creator->createCourse(
            'CS202',
            'Algorithms',
            null
        );

        $testCourseInstanceOne = $creator->createCourseInstance(
            $testCourseOne,
            $courseStart,
            $courseEnd
        );

        $testCourseInstanceOne
            ->setIndexInCourse(1)
            ->addInstructor($testInstructor);

        $testCourseInstanceTwo = $creator->createCourseInstance(
            $testCourseTwo,
            $courseStart,
            $courseEnd
        );

        $testCourseInstanceTwo
            ->setIndexInCourse(1)
            ->addInstructor($testInstructor);

        $creator->createEnrolment(
            $testStudent,
            $testCourseInstanceOne
        );

        $creator->createEnrolment(
            $testStudent,
            $testCourseInstanceTwo
        );

        $testLabBeforeTodayOne = $creator->createLab(
            'Lab One',
            $courseStart,
            $testCourseInstanceOne
        );

        $testLabBeforeTodayTwo = $creator->createLab(
            'Lab Two',
            $courseStart,
            $testCourseInstanceTwo
        );

        $testLabAfterToday = $creator->createLab(
            'Lab Three',
            $courseEnd,
            $testCourseInstanceTwo
        );

        $creator->createLabResponse(
            false,
            $testStudent,
            $testLabBeforeTodayOne
        );

        $creator->createLabResponse(
            true,
            $testStudent,
            $testLabBeforeTodayTwo
        );

        $creator->createLabResponse(
            false,
            $testStudent,
            $testLabAfterToday
        );
    }

    public function testAnonymousUserRedirected()
    {
        $client = static::createClient();
        $client->request('GET', '/courses');
        $this->assertResponseRedirects('/login', 302, "Anonymous user is redirected");
    }

    public function testStudentCourseLists()
    {
        $client = static::createClient();
        $client->loginUser($this->studentUser);
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseIsSuccessful("Page renders for student.");

        // All course links shown
        $courseTableLinkNodes = $crawler->filter('#course-list a')->extract(['_text', 'href']);

        $courseTableLinkTexts = array_map(function ($node) {
            return $node[0];
        }, $courseTableLinkNodes);

        $this->assertContains('CS101-Programming', $courseTableLinkTexts, "Course list contains first course text");
        $this->assertContains('CS202-Algorithms', $courseTableLinkTexts, "Course list contains second course text");

        $courseTableLinkHrefs = array_map(function ($node) {
            return $node[1];
        }, $courseTableLinkNodes);

        $this->assertContains("/courses/CS101/1/1234567", $courseTableLinkHrefs, "Course list contains link to first student course summary");
        $this->assertContains("/courses/CS202/1/1234567", $courseTableLinkHrefs, "Course list contains link to second student course summary");

        // Recent labs before date shown
        $recentLabsTableLinkNodes = $crawler->filter('#recent-pending-lab-list a')->extract(['_text', 'href']);

        $recentLabsTableLinkTexts = array_map(function ($node) {
            return $node[0];
        }, $recentLabsTableLinkNodes);

        $this->assertContains('Lab One', $recentLabsTableLinkTexts, "Pending list contains pending lab before today's date");
        $this->assertNotContains('Lab Two', $recentLabsTableLinkTexts, "Pending list does not contain completed lab");
        $this->assertNotContains('Lab Three', $recentLabsTableLinkTexts, "Labs list does not contain pending lab after today's date");

        $recentLabsTableLinkHrefs = array_map(function ($node) {
            return $node[1];
        }, $recentLabsTableLinkNodes);

        $this->assertContains('/courses/CS101/1/lab/lab-one/1234567/survey/1', $recentLabsTableLinkHrefs, "Pending list contains correct link to lab survey first page");
    }

    public function testInstructorCourseLists()
    {
        $client = static::createClient();
        $client->loginUser($this->instructorUser);
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseIsSuccessful("Page renders for instructor.");

        // All course links shown
        $courseTableLinkNodes = $crawler->filter('#course-list a')->extract(['_text', 'href']);

        $courseTableLinkTexts = array_map(function ($node) {
            return $node[0];
        }, $courseTableLinkNodes);

        $this->assertContains('CS101-Programming', $courseTableLinkTexts, "Course list contains first course text");
        $this->assertContains('CS202-Algorithms', $courseTableLinkTexts, "Course list contains second course text");

        $courseTableLinkHrefs = array_map(function ($node) {
            return $node[1];
        }, $courseTableLinkNodes);

        $this->assertContains("/courses/CS101/1", $courseTableLinkHrefs, "Course list contains link to first course summary");
        $this->assertContains("/courses/CS202/1", $courseTableLinkHrefs, "Course list contains link to second course summary");

        // Recent labs before date shown
        $recentLabsTableLinkNodes = $crawler->filter('#recent-lab-list a')->extract(['_text', 'href']);

        $recentLabsTableLinkTexts = array_map(function ($node) {
            return $node[0];
        }, $recentLabsTableLinkNodes);

        $this->assertContains('Lab One', $recentLabsTableLinkTexts, "Recent list contains first lab before today's date");
        $this->assertContains('Lab Two', $recentLabsTableLinkTexts, "Recent list contains second lab before today's date");
        $this->assertNotContains('Lab Three', $recentLabsTableLinkTexts, "Recent list does not contain lab after today's date");

        $recentLabsTableLinkHrefs = array_map(function ($node) {
            return $node[1];
        }, $recentLabsTableLinkNodes);

        $this->assertContains('/courses/CS101/1/lab/lab-one', $recentLabsTableLinkHrefs, "Pending list contains link to first lab summary");
        $this->assertContains('/courses/CS202/1/lab/lab-two', $recentLabsTableLinkHrefs, "Pending list contains correct link to first lab summary");
    }
}
