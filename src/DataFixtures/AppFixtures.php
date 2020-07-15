<?php

namespace App\DataFixtures;

use App\Entity\Instructor;
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

    const TEST_STUDENT_USERNAME = 'test@student.gla.ac.uk';
    const TEST_INSTUCTOR_USERNAME = 'test@glasgow.ac.uk';

    private $logger;
    private $manager;
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
        $this->manager = $manager;

        // Creates faker for mocking data
        $this->faker = Faker\Factory::create('en_GB');

        $testUsers = $this->loadFunctionalTestUsers();
        $this->printArray('Test Users', $testUsers);

        // Create students
        $students = $this->loadStudents();
        $this->printArray('Students', $students);

        // Create instructors
        $instructors = $this->loadInstructors();
        $this->printArray('Instructors', $instructors);

        // Commit to db
        $manager->flush();
    }

    private function createUser(callable $roleSpecificConfigFunction)
    {
         $user = new User();
         $forename = $this->faker->firstname;
         $surname = $this->faker->lastname;
         $user->setForename($forename);
         $user->setSurname($surname);
         $user->setPassword(self::PASSWORD);

         $roleSpecificConfigFunction($user);

         // Add to db commit
         $this->manager->persist($user);

    }

    private function loadFunctionalTestUsers() : array
    {
        $testStudentGuid = $this->faker->unique()->passthrough(1234567);
        $testStudentEmail = self::TEST_STUDENT_USERNAME;
        $testStudent = $this->createStudent(
            $guid = $testStudentGuid,
            $email = $testStudentEmail
        );

        $testInstructorEmail = self::TEST_INSTUCTOR_USERNAME;
        $testInstructor = $this->createInstructor(
            $email = $testInstructorEmail
        );

        return [
            'student' => $testStudent,
            'instructor' => $testInstructor
        ];
    }

    private function createStudent($guid = null, $email = null) : Student
    {
        $student = new Student();
        $guid = $guid ? $guid : $this->faker->unique()->randomNumber(7);
        $student->setGuid($guid);

        // Add to db
        $this->manager->persist($student);

        $this->createUser(function($user) use ($guid, $email, $student) {
            if (!$email) {
                $firstSurnameLetter = strtolower($user->getSurname()[0]);
                $email = $guid . $firstSurnameLetter . '@student.gla.ac.uk';
            }
            $user->setEmail($email);
            $user->setStudent($student);
        });

        return $student;
    }

    private function loadStudents() : array
    {
        $students = [];

        for ($i = 0; $i < 10; $i++) {
            $students[] = $this->createStudent();
        }

        return $students;
    }

    private function createInstructor($email = null) : Instructor
    {
        $instructor = new Instructor();

        // Add to db
        $this->manager->persist($instructor);

        $this->createUser(function($user) use ($email, $instructor) {
            if (!$email) {
                $email = $user->getForename() . '.' . $user->getSurname() . '@glasgow.ac.uk';
                $email = strtolower($email);
            }
            $user->setEmail($email);
            $user->setInstructor($instructor);
        });

        return $instructor;
    }

    private function loadInstructors() : array
    {
        $instructors = [];

        for ($i = 0; $i < 5; $i++) {
            $instructors[] = $this->createInstructor();
        }

        return $instructors;

    }

    // HELPER FUNCTIONS

    /**
     * Helper function for printing fixtures that have been generated.
     *
     * @param string $title to display above fixtures
     * @param array $array of objects to print
     * @return void
     */
    private function printArray(string $title, array $array) {
        printf("%s\n-----\n \n", $title);
        foreach ($array as $str) {
            printf($str);
        }
        printf("\n");
    }
}
