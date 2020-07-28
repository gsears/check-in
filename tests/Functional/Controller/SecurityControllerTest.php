<?php

namespace App\Tests\Functional\Controller;

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

    public function userProvider()
    {
        yield [$this->getEntityCreator()->createInstructor('test', 'test')];
        yield [$this->getEntityCreator()->createStudent('test', 'test', '1234')];
    }

    /**
     * @dataProvider userProvider
     *
     * Assert that clicking the logout button logs out for all users
     */
    public function testLogout($userType)
    {
        $client = static::createClient();
        $client->loginUser($userType->getUser());
        $client->request('GET', '/courses');
        $this->assertResponseIsSuccessful();

        $client->clickLink("Logout");

        // Use full path as we are redirecting from firewall in security.yaml
        $this->assertResponseRedirects('http://localhost/login', 302);
    }
}
