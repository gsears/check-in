<?php

namespace App\Tests\Functional\Entity;

use App\Entity\Lab;
use App\Entity\LabResponse;
use App\Entity\Student;
use App\Tests\Functional\FunctionalTestCase;

class LabResponseTest extends FunctionalTestCase
{
    public function testTimestamps()
    {
        $em = $this->getEntityManager();

        $creator = $this->getEntityCreator();

        $student = $creator->createStudent(
            'name',
            'surname',
            '123456'
        );

        $course = $creator->createCourse(
            '123',
            'test',
            null
        );

        $courseInstance = $creator->createCourseInstance(
            $course,
            date_create('1pm 20 November 2020'),
            date_create('2pm 20 November 2020')
        );

        $lab = $creator->createLab(
            'name',
            date_create('20 November 2020'),
            $courseInstance
        );

        $labResponse = new LabResponse();
        $labResponse->setSubmitted(false);
        $labResponse->setStudent($student);
        $labResponse->setLab($lab);

        $this->assertNull($labResponse->getCreatedAt());
        $this->assertNull($labResponse->getUpdatedAt());

        $em->persist($labResponse);
        $em->flush();

        $createdAt = $labResponse->getCreatedAt();
        $firstUpdate = $labResponse->getUpdatedAt();

        $this->assertNotNull($createdAt);
        $this->assertNotNull($firstUpdate);
        $this->assertEquals($createdAt, $firstUpdate);

        $labResponse->setSubmitted(true);

        $em->flush();

        $secondUpdate = $labResponse->getUpdatedAt();

        $this->assertNotEquals($firstUpdate, $secondUpdate);
    }
}