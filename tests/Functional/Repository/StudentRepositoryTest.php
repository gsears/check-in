<?php

/*
StudentRepositoryTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional\Repository;

use DateTime;
use App\Entity\Student;
use App\Tests\Functional\FunctionalTestCase;

/**
 * Tests database query methods
 */
class StudentRepositoryTest extends FunctionalTestCase
{
    public function testFindByCourseInstance()
    {
        $creator = $this->getEntityCreator();

        $course = $creator->createCourse(
            '1234',
            'test_course',
            null
        );

        $courseInstanceOne = $creator->createCourseInstance(
            $course,
            new DateTime('20 November 2020'),
            new DateTime('21 November 2020')
        );

        $courseInstanceTwo = $creator->createCourseInstance(
            $course,
            new DateTime('20 November 2020'),
            new DateTime('21 November 2020')
        );

        $courseInstanceThree = $creator->createCourseInstance(
            $course,
            new DateTime('20 November 2020'),
            new DateTime('21 November 2020')
        );

        $studentOne = $creator->createStudent(
            'firstname',
            'surname',
            '12345'
        );

        $creator->createEnrolment(
            $studentOne,
            $courseInstanceOne
        );

        $studentTwo = $creator->createStudent(
            'firstname2',
            'surname2',
            '54321'
        );

        $creator->createEnrolment(
            $studentTwo,
            $courseInstanceTwo
        );

        $em = $this->getEntityManager();

        $em->flush(); // Update all assignments

        /**
         * @var StudentRepository
         */
        $repo = $em->getRepository(Student::class);

        $this->assertEquals([$studentOne], $repo->findByCourseInstance($courseInstanceOne));
        $this->assertEquals([$studentTwo], $repo->findByCourseInstance($courseInstanceTwo));
        $this->assertEquals([], $repo->findByCourseInstance($courseInstanceThree));
    }
}
