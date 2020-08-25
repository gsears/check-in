<?php

/*
LabRepositoryTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional\Repository;

use DateTime;
use App\Entity\Lab;
use App\Tests\Functional\FunctionalTestCase;

class LabRepositoryTest extends FunctionalTestCase
{
    public function testFindByCourseInstanceBeforeDate()
    {
        $creator = $this->getEntityCreator();

        $course = $creator->createCourse(
            'testCourse',
            'test',
            null
        );

        $courseInstanceOne = $creator->createCourseInstance(
            $course,
            new DateTime("20 November 2020"),
            new DateTime("25 November 2020")
        );

        $courseInstanceTwo = $creator->createCourseInstance(
            $course,
            new DateTime("20 November 2020"),
            new DateTime("25 November 2020")
        );

        $student = $creator->createStudent(
            'test',
            'test',
            '1234'
        );

        $enrolment = $creator->createEnrolment(
            $student,
            $courseInstanceOne
        );

        $labOneCourseInstanceOne = $creator->createLab(
            'testLab',
            new DateTime("20 November 2020"),
            $courseInstanceOne
        );

        $labTwoCourseInstanceOne = $creator->createLab(
            'testLab',
            new DateTime("21 November 2020"),
            $courseInstanceOne
        );

        //  This should not be present.
        $labThreeCourseInstanceOne = $creator->createLab(
            'testLab',
            new DateTime("23 November 2020"),
            $courseInstanceOne
        );

        //  This should not be present.
        $labOneCourseInstanceTwo = $creator->createLab(
            'testLab',
            new DateTime("20 November 2020"),
            $courseInstanceTwo
        );

        /**
         * @var LabRepository
         */
        $repo = $this->getEntityManager()->getRepository(Lab::class);
        $testLabs = $repo->findByCourseInstanceBeforeDate($courseInstanceOne, new DateTime("22 November 2020"));

        $this->assertEquals(2, count($testLabs));
        $this->assertContains($labOneCourseInstanceOne, $testLabs);
        $this->assertContains($labTwoCourseInstanceOne, $testLabs);
    }
}
