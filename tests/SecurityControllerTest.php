<?php

namespace App\Tests;

use App\Tests\Functional\FunctionalTestCase;

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

    /**
     * Assert that clicking the logout button logs out.
     */
    public function testLogout()
    {
        $client = static::createClient();

        $testInstructor = $this->getEntityCreator()->createInstructor(
            'test',
            'test',
        );

        // simulate $testUser being logged in
        $client->loginUser($testInstructor->getUser());

        $crawler = $client->request('GET', '/courses');

        $this->assertResponseIsSuccessful();

        $client->clickLink("Logout");

        // Use full path as we are redirecting from firewall in security.yaml
        $this->assertResponseRedirects('http://localhost/login', 302);
    }
}
