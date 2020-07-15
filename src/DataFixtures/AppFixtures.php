<?php

namespace App\DataFixtures;

use App\Entity\Student;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Faker;

/**
 * Populates the database with dummy data for testing and evaluation.
 */
class AppFixtures extends Fixture
{
    // 'password', pre-hashed for speed
    const PASSWORD = '$2y$13$M5wzRvyIISgDAjtGJEna5.mnz5QAgsoExUOPEwacu/cOFSz861fjC';

    private $logger;
    private $faker;

    /**
     * Inject required services via Symfony dependency injection.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function load(ObjectManager $manager)
    {
        // Creates faker for mocking data
        $this->faker = Faker\Factory::create('en_GB');
        $students = $this->loadStudents($manager);
        foreach ($students as $student) {
            printf($student);
        }

        $manager->flush();
    }

    private function loadStudents(ObjectManager $manager)
    {
        $students = [];

        for ($i = 0; $i < 10; $i++) {
            // Create student
            $student = new Student();
            $guid = $this->faker->unique()->randomNumber(7);
            $student->setGuid($guid);

            // Add to db
            $manager->persist($student);

            // Create user
            $user = new User();
            $forename = $this->faker->firstname;
            $surname = $this->faker->lastname;
            $firstSurnameLetter = strtolower($surname[0]);

            $user->setForename($forename);
            $user->setSurname($surname);
            $user->setEmail($guid . $firstSurnameLetter . '@student.gla.ac.uk');
            $user->setPassword(self::PASSWORD);
            $user->setStudent($student);

            // Add to db
            $manager->persist($user);

            // Add student to students array
            $students[] = $student;
        }

        return $students;
    }

    private function loadInstructors()
    {

    }
}
