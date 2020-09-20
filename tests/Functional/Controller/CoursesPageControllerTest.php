<?php

/*
CoursesPageTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\FunctionalTestCase;

/**
 * Checks security / user access of courses page.
 */
class CoursesPageControllerTest extends FunctionalTestCase
{
    public function testAnonymousUserRedirected()
    {
        $client = static::createClient();
        $client->request('GET', '/courses');
        $this->assertResponseRedirects('/login', 302, "Anonymous user is not redirected");
    }

    public function testStudentAccess()
    {
        $creator = $this->getEntityCreator();

        $student = $creator->createStudent(
            'studentName',
            'studentSurname',
            '1234567'
        );

        $client = static::createClient();
        $client->loginUser($student->getUser());
        $client->request('GET', '/courses');
        $this->assertResponseIsSuccessful("Page does not render for student.");
    }

    public function testInstructorAccess()
    {
        $creator = $this->getEntityCreator();

        $instructor = $creator->createInstructor(
            'instructorName',
            'instructorSurname'
        );

        $client = static::createClient();
        $client->loginUser($instructor->getUser());
        $client->request('GET', '/courses');
        $this->assertResponseIsSuccessful("Page does not render for instructor.");
    }
}
