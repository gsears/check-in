<?php

namespace App\Tests\Functional\Controller;

use App\Provider\DateTimeProvider;
use App\Tests\Functional\FunctionalTestCase;

class CourseControllerTest extends FunctionalTestCase
{
    public function testCourseIndexSecurity()
    {
        $client = static::createClient();
        $client->request('GET', '/courses');
        $this->assertResponseRedirects('/login', 302, "Anonymous user is redirected");
    }

    public function testCourseIndexStudent()
    {
        $dateTimeProvider = new DateTimeProvider();
        $courseStart =  ($dateTimeProvider->getCurrentDateTime()->modify("- 1 week"));
        $courseEnd = ($dateTimeProvider->getCurrentDateTime()->modify("+ 1 week"));

        $creator = $this->getEntityCreator();
        // Displays all course links for that student.
        $testStudent = $creator->createStudent(
            'test',
            'test',
            '1234567'
        );

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

        $testCourseInstanceOne->setIndexInCourse(1);

        $testCourseInstanceTwo = $creator->createCourseInstance(
            $testCourseTwo,
            $courseStart,
            $courseEnd
        );

        $testCourseInstanceOne->setIndexInCourse(1);

        $creator->createEnrolment(
            $testStudent,
            $testCourseInstanceOne
        );

        $creator->createEnrolment(
            $testStudent,
            $testCourseInstanceTwo
        );

        $testLabBeforeTodayOne = $creator->createLab(
            'labOne',
            $courseStart,
            $testCourseInstanceOne
        );

        $testLabBeforeTodayTwo = $creator->createLab(
            'labTwo',
            $courseStart,
            $testCourseInstanceTwo
        );

        $testLabAfterToday = $creator->createLab(
            'labThree',
            $courseEnd,
            $testCourseInstanceTwo
        );

        $responseBeforeTodayPending = $creator->createLabResponse(
            false,
            $testStudent,
            $testLabBeforeTodayOne
        );

        $responseBeforeTodayDone = $creator->createLabResponse(
            true,
            $testStudent,
            $testLabBeforeTodayTwo
        );

        $responseAfterTodayPending = $creator->createLabResponse(
            false,
            $testStudent,
            $testLabAfterToday
        );

        $client = static::createClient();
        $client->loginUser($testStudent->getUser());
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

        $this->assertContains("/courses/CS101/1/1234567", $courseTableLinkHrefs, "Course list contains first course link");
        $this->assertContains("/courses/CS202/1/1234567", $courseTableLinkHrefs, "Course list contains second course link");

        // Recent labs before date shown
        $recentLabsTableLinkNodes = $crawler->filter('#recent-pending-lab-list a')->extract(['_text', 'href']);

        $recentLabsTableLinkTexts = array_map(function ($node) {
            return $node[0];
        }, $recentLabsTableLinkNodes);

        $this->assertContains('labOne', $recentLabsTableLinkTexts, "Pending list contains pending lab before today's date");
        $this->assertNotContains('labTwo', $recentLabsTableLinkTexts, "Pending list does not contain completed lab");
        $this->assertNotContains('labThree', $recentLabsTableLinkTexts, "Labs list does not contain pending lab after today's date");

        $recentLabsTableLinkHrefs = array_map(function ($node) {
            return $node[1];
        }, $recentLabsTableLinkNodes);

        $this->assertContains('/courses/CS101/1/lab/labOne/1234567/survey/1', $recentLabsTableLinkHrefs, "Pending list contains correct link to lab survey first page");
    }

    // TODO: Instructor


}
