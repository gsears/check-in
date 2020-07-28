<?php

namespace App\Tests\Functional\Repository;

use App\Entity\CourseInstance;
use App\Tests\Functional\FunctionalTestCase;

class CourseInstanceRepositoryTest extends FunctionalTestCase
{
    public function testFindByStudent()
    {
        $creator = $this->getEntityCreator();

        $course = $creator->createCourse(
            '1234',
            'test_course',
            null
        );

        $student = $creator->createStudent(
            'firstname',
            'surname',
            '1234'
        );

        $student2 = $creator->createStudent(
            'firstname',
            'surname',
            '4321'
        );

        $expectedCourseInstances = [];

        // Add expected student course instances every even index
        for ($i = 0; $i < 10; $i++) {

            $isEven = $this->isEvenFunction($i);

            $courseInstance = $creator->createCourseInstance(
                $course,
                date_create("20 November 2020"),
                date_create("21 November 2020")
            );

            $creator->createEnrolment(
                $isEven ? $student : $student2,
                $courseInstance
            );

            if ($isEven) {
                $expectedCourseInstances[] = $courseInstance;
            }
        }

        $repo = $this->getEntityManager()->getRepository(CourseInstance::class);

        $courseInstances = $repo->findByStudent($student);

        $this->assertEquals($expectedCourseInstances, $courseInstances);
    }

    public function testFindByInstructor()
    {
        $creator = $this->getEntityCreator();

        $course = $creator->createCourse(
            '1234',
            'test_course',
            null
        );

        $instructor1 = $creator->createInstructor(
            'firstname',
            'surname'
        );

        $instructor2 = $creator->createInstructor(
            'firstname2',
            'surname2',
        );

        $expectedCourseInstances = [];

        // Add expected student course instances every even index
        for ($i = 0; $i < 10; $i++) {

            $isEven = $this->isEvenFunction($i);

            $courseInstance = $creator->createCourseInstance(
                $course,
                date_create("20 November 2020"),
                date_create("21 November 2020")
            );

            $courseInstance->addInstructor($isEven ? $instructor1 : $instructor2);

            if ($isEven) {
                $expectedCourseInstances[] = $courseInstance;
            }
        }

        $repo = $this->getEntityManager()->getRepository(CourseInstance::class);

        $courseInstances = $repo->findByInstructor($instructor1);

        $this->assertEquals($expectedCourseInstances, $courseInstances);
    }

    public function testFindIfMatchesCourse()
    {
        $creator = $this->getEntityCreator();

        $course1 = $creator->createCourse(
            '1234',
            'test_course',
            null
        );

        $course2 = $creator->createCourse(
            '4321',
            'test_course',
            null
        );

        $courseInstance1 = $creator->createCourseInstance(
            $course1,
            date_create("20 November 2020"),
            date_create("21 November 2020")
        );

        $courseInstance2 = $creator->createCourseInstance(
            $course2,
            date_create("20 November 2020"),
            date_create("21 November 2020")
        );

        $repo = $this->getEntityManager()->getRepository(CourseInstance::class);

        $this->assertEquals($courseInstance1, $repo->findIfMatchesCourse($courseInstance1, $course1));
        $this->assertNull($repo->findIfMatchesCourse($courseInstance2, $course1));
        $this->assertNull($repo->findIfMatchesCourse($courseInstance1, $course2));
    }

    public function isEvenFunction(int $i)
    {
        return $i % 2 === 0;
    }
}
