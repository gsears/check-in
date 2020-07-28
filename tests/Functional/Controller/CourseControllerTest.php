<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\FunctionalTestCase;

class CourseControllerTest extends FunctionalTestCase
{
    public function testAnonRedirected()
    {
        $client = static::createClient();
        $client->request('GET', '/courses');
        $this->assertResponseRedirects('/login', 302);
    }
}
