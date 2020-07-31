<?php

namespace App\Tests\Functional\Controller\CourseController;

use App\Provider\DateTimeProvider;
use App\Tests\Functional\FunctionalTestCase;

class CourseSummaryPageTest extends FunctionalTestCase
{
    public function testAnonymousUserRedirected()
    {
        $creator = $this->getEntityCreator();

        $dateTimeProvider = new DateTimeProvider();
        $courseStart =  ($dateTimeProvider->getCurrentDateTime()->modify("- 1 week"));
        $courseEnd = ($dateTimeProvider->getCurrentDateTime()->modify("+ 1 week"));


        $testCourseOne = $creator->createCourse(
            'CS101',
            'Programming',
            null
        );

        $testCourseInstanceOne = $creator->createCourseInstance(
            $testCourseOne,
            $courseStart,
            $courseEnd
        );

        $testCourseInstanceOne
            ->setIndexInCourse(1);

        $client = static::createClient();
        $client->request('GET', '/courses/CS101/1');
        $this->assertResponseRedirects('/login', 302, "Anonymous user is redirected");
    }
}
