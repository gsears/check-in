<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Instructor;
use App\Tests\Functional\FunctionalTestCase;

class InstructorRepositoryTest extends FunctionalTestCase
{
    public function testFindByStudent()
    {
        $creator = $this->getEntityCreator();

        $course = $creator->createCourse(
            '1234',
            'test_course',
            null
        );

        $student1 = $creator->createStudent(
            'firstname',
            'surname',
            '12345'
        );

        $student2 = $creator->createStudent(
            'firstname2',
            'surname2',
            '54321'
        );

        $instructor1 = $creator->createInstructor(
            'firstname',
            'surname'
        );

        $instructor2 = $creator->createInstructor(
            'firstname2',
            'surname2'
        );

        // Student 1 = Instructor 1 and 2.

        $courseInstance1 = $creator->createCourseInstance(
            $course,
            date_create("20 November 2020"),
            date_create("21 November 2020")
        );

        $creator->createEnrolment(
            $student1,
            $courseInstance1
        );

        $courseInstance1->addInstructor($instructor1);
        $courseInstance1->addInstructor($instructor2);

        // Student 2 = Instructor 2.

        $courseInstance2 = $creator->createCourseInstance(
            $course,
            date_create("20 November 2020"),
            date_create("21 November 2020")
        );

        $creator->createEnrolment(
            $student2,
            $courseInstance2
        );

        $courseInstance2->addInstructor($instructor2);

        $em = $this->getEntityManager();

        $em->flush(); // Update all assignments

        $repo = $em->getRepository(Instructor::class);

        $this->assertEquals([$instructor1, $instructor2], $repo->findByStudent($student1));
        $this->assertEquals([$instructor2], $repo->findByStudent($student2));
    }

    public function isEvenFunction(int $i)
    {
        return $i % 2 === 0;
    }
}
