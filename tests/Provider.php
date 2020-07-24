<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AbstractFunctionalTest extends WebTestCase
{
    public function usernameProvider()
    {
        yield [AppFixtures::TEST_STUDENT_USERNAME];
        yield [AppFixtures::TEST_INSTUCTOR_USERNAME];
    }

    public function urlProvider()
    {
        yield ['/courses'];
    }
}
