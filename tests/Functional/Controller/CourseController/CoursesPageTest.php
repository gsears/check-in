<?php

namespace App\Tests\Functional\Controller\CourseController;

use App\Provider\DateTimeProvider;
use App\Tests\Functional\FunctionalTestCase;

class CoursesPageTest extends FunctionalTestCase
{

    public function testInstructorCourseLists()
    {
        $dateTimeProvider = new DateTimeProvider();
        $courseStart =  ($dateTimeProvider->getCurrentDateTime()->modify("- 1 week"));
        $courseEnd = ($dateTimeProvider->getCurrentDateTime()->modify("+ 1 week"));

        $creator = $this->getEntityCreator();

        $testInstructor = $creator->createInstructor(
            'instructorName',
            'instructorSurname',
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

        $creator->createLab(
            'Lab One',
            $courseStart,
            $testCourseInstanceOne
        );

        $creator->createLab(
            'Lab Two',
            $courseStart,
            $testCourseInstanceTwo
        );

        $creator->createLab(
            'Lab Three',
            $courseEnd,
            $testCourseInstanceTwo
        );

        $client = static::createClient();
        $client->loginUser($testInstructor->getUser());
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

    public function testAnonymousUserRedirected()
    {
        $client = static::createClient();
        $client->request('GET', '/courses');
        $this->assertResponseRedirects('/login', 302, "Anonymous user is redirected");
    }

    public function testStudentCourseLists()
    {
        $dateTimeProvider = new DateTimeProvider();
        $courseStart =  ($dateTimeProvider->getCurrentDateTime()->modify("- 1 week"));
        $courseEnd = ($dateTimeProvider->getCurrentDateTime()->modify("+ 1 week"));

        $creator = $this->getEntityCreator();

        $testStudent = $creator->createStudent(
            'studentName',
            'studentSurname',
            '7654321'
        );

        $testCourseOne = $creator->createCourse(
            'CS303',
            'Programming',
            null
        );

        $testCourseTwo = $creator->createCourse(
            'CS404',
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

        $testCourseInstanceTwo->setIndexInCourse(1);

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

        $client = static::createClient();
        $client->loginUser($testStudent->getUser());
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseIsSuccessful("Page renders for student.");

        // All course links shown
        $courseTableLinkNodes = $crawler->filter('#course-list a')->extract(['_text', 'href']);

        $courseTableLinkTexts = array_map(function ($node) {
            return $node[0];
        }, $courseTableLinkNodes);

        $this->assertContains('CS303-Programming', $courseTableLinkTexts, "Course list contains first course text");
        $this->assertContains('CS404-Algorithms', $courseTableLinkTexts, "Course list contains second course text");

        $courseTableLinkHrefs = array_map(function ($node) {
            return $node[1];
        }, $courseTableLinkNodes);

        $this->assertContains("/courses/CS303/1/7654321", $courseTableLinkHrefs, "Course list contains link to first student course summary");
        $this->assertContains("/courses/CS404/1/7654321", $courseTableLinkHrefs, "Course list contains link to second student course summary");

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

        $this->assertContains('/courses/CS303/1/lab/lab-one/7654321/survey/1', $recentLabsTableLinkHrefs, "Pending list contains correct link to lab survey first page");
    }
}
