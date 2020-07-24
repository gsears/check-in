<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\UrlHelper;

class SecurityControllerTest extends WebTestCase
{
    public function usernameProvider()
    {
        yield [AppFixtures::TEST_STUDENT_USERNAME];
        yield [AppFixtures::TEST_INSTUCTOR_USERNAME];
    }

    public function testRootRedirectsToCourses()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        // Use full path as we are redirecting from firewall in security.yaml
        $this->assertResponseRedirects("http://localhost/courses", 301);
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

    /**
     * @dataProvider usernameProvider
     *
     * Assert that clicking the logout button logs out.
     */
    public function testLogout(string $username)
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail($username);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/courses');
        $client->clickLink('Logout');

        // Use full path as we are redirecting from firewall in security.yaml
        $this->assertResponseRedirects('http://localhost/login', 302);
    }
}
