<?php

namespace App\Tests;

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

        $isEvenFunction = function ($i) {
            return $i % 2 === 0;
        };

        // Add expected student course instances every even index
        for ($i = 0; $i < 10; $i++) {

            $courseInstance = $creator->createCourseInstance(
                $course,
                date_create("20 November 2020"),
                date_create("21 November 2020")
            );

            $creator->createEnrolment(
                $isEvenFunction($i) ? $student : $student2,
                $courseInstance
            );

            if ($isEvenFunction($i)) {
                $expectedCourseInstances[] = $courseInstance;
            }
        }

        $repo = $this->getEntityManager()->getRepository(CourseInstance::class);

        $courseInstances = $repo->findByStudent($student);

        $this->assertEquals($expectedCourseInstances, $courseInstances);
    }
}
