<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CourseControllerTest extends WebTestCase
{
    public function testUnauthorisedUsersRedirected() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseRedirects('/login', 302);
    }
}
