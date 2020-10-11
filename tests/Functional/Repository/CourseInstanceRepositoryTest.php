<?php

/*
CourseInstanceRepositoryTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional\Repository;

use DateTime;
use App\Entity\CourseInstance;
use App\Tests\Functional\FunctionalTestCase;

/**
 * Tests database query methods
 */
class CourseInstanceRepositoryTest extends FunctionalTestCase
{
    public function testFindAllActive()
    {
        $creator = $this->getEntityCreator();

        $course = $creator->createCourse(
            '1234',
            'test_course',
            null
        );

        $testCurrentDate = new DateTime('2pm 20 November 2020');

        $activeCourseInstanceOne = $creator->createCourseInstance(
            $course,
            new DateTime('2pm 20 November 2020'),
            new DateTime('2pm 21 November 2020')
        );

        $activeCourseInstanceTwo = $creator->createCourseInstance(
            $course,
            new DateTime('2pm 19 November 2020'),
            new DateTime('2pm 21 November 2020')
        );

        $inactiveCourseInstanceOne = $creator->createCourseInstance(
            $course,
            new DateTime('2pm 19 November 2020'),
            new DateTime('1pm 20 November 2020')
        );

        $inactiveCourseInstanceTwo = $creator->createCourseInstance(
            $course,
            new DateTime('2pm 21 November 2020'),
            new DateTime('1pm 22 November 2020')
        );

        /**
         * @var CourseInstanceRepository
         */
        $repo = $this->getEntityManager()->getRepository(CourseInstance::class);
        $testResult = $repo->findAllActive($testCurrentDate);

        $this->assertContains($activeCourseInstanceOne, $testResult);
        $this->assertContains($activeCourseInstanceTwo, $testResult);
        $this->assertNotContains($inactiveCourseInstanceOne, $testResult);
        $this->assertNotContains($inactiveCourseInstanceTwo, $testResult);
    }

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
            '12345',
        );

        $student2 = $creator->createStudent(
            'firstname',
            'surname',
            '54321'
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

        /**
         * @var CourseInstanceRepository
         */
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

        /**
         * @var CourseInstanceRepository
         */
        $repo = $this->getEntityManager()->getRepository(CourseInstance::class);
        $courseInstances = $repo->findByInstructor($instructor1);

        $this->assertEquals($expectedCourseInstances, $courseInstances);
    }

    public function testGetNextIndexInCourse()
    {
        $creator = $this->getEntityCreator();

        $course = $creator->createCourse(
            '1234',
            'test_course',
            null
        );

        $courseInstanceOne = $creator->createCourseInstance(
            $course,
            new DateTime('2pm 20 November 2020'),
            new DateTime('2pm 21 November 2020')
        );

        $courseInstanceTwo = $creator->createCourseInstance(
            $course,
            new DateTime('2pm 19 November 2020'),
            new DateTime('2pm 21 November 2020')
        );

        /**
         * @var CourseInstanceRepository
         */
        $repo = $this->getEntityManager()->getRepository(CourseInstance::class);

        $this->assertEquals(3, $repo->getNextIndexInCourse($course));
    }

    public function testIndexExistsInCourse()
    {
        $creator = $this->getEntityCreator();

        $course = $creator->createCourse(
            '1234',
            'test_course',
            null
        );

        $courseInstanceOne = $creator->createCourseInstance(
            $course,
            new DateTime('2pm 20 November 2020'),
            new DateTime('2pm 21 November 2020')
        );

        /**
         * @var CourseInstanceRepository
         */
        $repo = $this->getEntityManager()->getRepository(CourseInstance::class);

        $this->assertTrue($repo->indexExistsInCourse(1, $course));
        $this->assertFalse($repo->indexExistsInCourse(2, $course));
    }

    public function testFindByIndexAndCourseCode()
    {
        $creator = $this->getEntityCreator();

        $course = $creator->createCourse(
            '1234',
            'test_course',
            null
        );

        $courseInstanceOne = $creator->createCourseInstance(
            $course,
            new DateTime('2pm 20 November 2020'),
            new DateTime('2pm 21 November 2020')
        );

        $courseInstanceTwo = $creator->createCourseInstance(
            $course,
            new DateTime('2pm 19 November 2020'),
            new DateTime('2pm 21 November 2020')
        );

        /**
         * @var CourseInstanceRepository
         */
        $repo = $this->getEntityManager()->getRepository(CourseInstance::class);

        $this->assertEquals($courseInstanceOne, $repo->findByIndexAndCourseCode(1, '1234'));
        $this->assertEquals($courseInstanceTwo, $repo->findByIndexAndCourseCode(2, '1234'));
        $this->assertNull($repo->findByIndexAndCourseCode(3, '1234'));
    }

    private function isEvenFunction(int $i)
    {
        return $i % 2 === 0;
    }
}
