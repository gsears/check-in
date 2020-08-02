<?php

/*
SecurityControllerTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\FunctionalTestCase;

/**
 * Tests for the SecurityController
 */
class SecurityControllerTest extends FunctionalTestCase
{

    public function testRootRedirectsToCourses()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        // Use full path as we are redirecting from firewall in security.yaml
        $this->assertResponseRedirects("http://localhost/courses", 301);
    }

    /**
     * Assert that users are redirected to their courses page on login
     */
    public function testLoginRedirect()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form();

        $testUser = $this->getEntityCreator()->createUser(
            'test',
            'test',
            'test@test.com'
        );


        $client->submit($form, [
            'email' => $testUser->getEmail(),
            'password' => 'password',
        ]);

        $this->assertResponseRedirects('/courses', 302);
    }

    /**
     * Assert that users are redirected to their courses page if they access login page
     */
    public function testLoginPageRedirectIfLoggedIn()
    {
        $client = static::createClient();

        $testUser = $this->getEntityCreator()->createUser(
            'test',
            'test',
            'test@test.com'
        );

        // simulate $testUser being logged in
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/login');
        $this->assertResponseRedirects('/courses', 302);
    }

    public function testLogoutStudent()
    {
        $student = $this->getEntityCreator()->createStudent('test', 'test', '1234');

        $client = static::createClient();
        $client->loginUser($student->getUser());
        $client->request('GET', '/courses');
        $this->assertResponseIsSuccessful();

        $client->clickLink("Logout");

        // Use full path as we are redirecting from firewall in security.yaml
        $this->assertResponseRedirects('http://localhost/login', 302);
    }

    public function testLogoutInstructor()
    {
        $instructor = $this->getEntityCreator()->createInstructor('test', 'test');

        $client = static::createClient();
        $client->loginUser($instructor->getUser());
        $client->request('GET', '/courses');
        $this->assertResponseIsSuccessful();

        $client->clickLink("Logout");

        // Use full path as we are redirecting from firewall in security.yaml
        $this->assertResponseRedirects('http://localhost/login', 302);
    }
}
