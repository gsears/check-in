<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\UrlHelper;

class SecurityControllerTest extends WebTestCase
{
    public function usernameProvider() {
        yield [AppFixtures::TEST_STUDENT_USERNAME];
        yield [AppFixtures::TEST_INSTUCTOR_USERNAME];
    }

    /**
     * @dataProvider usernameProvider
     *
     * Assert that users are redirected to their courses page on login
     */
    public function testLoginRedirect(string $username)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form();

        $client->submit($form, [
            'email' => $username,
            'password' => 'password',
        ]);

        $this->assertResponseRedirects('/courses', 302);
    }
}
