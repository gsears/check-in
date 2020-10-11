<?php

/*
LabResponseTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional\Entity;

use App\Entity\LabResponse;
use App\Tests\Functional\FunctionalTestCase;

/**
 * Tests automatic generation of timestamps on database hooks.
 */
class LabResponseTest extends FunctionalTestCase
{
    public function testTimestampsSetOnCreateAndUpdate()
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
