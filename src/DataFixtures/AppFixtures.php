<?php

namespace App\DataFixtures;

use App\Entity\AffectiveField;
use App\Entity\Course;
use App\Entity\CourseInstance;
use App\Entity\Enrolment;
use App\Entity\Instructor;
use App\Entity\LabSurvey;
use App\Entity\LabSurveyXYQuestion;
use App\Entity\Student;
use App\Entity\User;
use App\Entity\XYQuestion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Faker;

/**
 * Populates the database with dummy data for testing and evaluation.
 *
 * TODO: Some extra blurb saying why this is a monolithic file
 */
class AppFixtures extends Fixture
{
    // 'password', pre-hashed for speed
    const PASSWORD = '$2y$13$M5wzRvyIISgDAjtGJEna5.mnz5QAgsoExUOPEwacu/cOFSz861fjC';

    const TEST_STUDENT_USERNAME = 'test@student.gla.ac.uk';
    const TEST_INSTUCTOR_USERNAME = 'test@glasgow.ac.uk';

    const COURSE_TITLE_TEMPLATES = [
        '%s Theory and Applications',
        'Advanced %s',
        'Introductory %s',
        '%s Engineering',
        'Functional %s',
        'Object Orientated %s',
        'Safety Critical %s'
    ];

    const COURSE_TITLE_SUBJECTS = [
        'Cloud Storage',
        'Big Data',
        'Java',
        'Python',
        'Algorithms',
        'Database',
        'Software',
        'Quantum Computing',
        'Systems',
        'Networking',
    ];

    const TERM_DATES = [
        'firstTerm' => [
            'start' => '21 September 2020',
            'end' => '04 December 2020',
        ],
        'secondTerm' => [
            'start' => '11 January 2021',
            'end' => '26 March 2021',
        ]
    ];

    const AFFECTIVE_FIELD_INTEREST = 'interest';
    const AFFECTIVE_FIELD_DIFFICULTY = 'difficulty';
    const AFFECTIVE_FIELD_FAMILIARITY = 'familiarity';
    const AFFECTIVE_FIELD_PLAN = 'ability to plan';
    const AFFECTIVE_FIELD_IMPROVEMENT = 'improvement';
    const AFFECTIVE_FIELD_SATISFACTION = 'satisfaction';

    const AFFECTIVE_FIELDS = [
        self::AFFECTIVE_FIELD_DIFFICULTY => [
            'high' => 'hard',
            'low' => 'easy'
        ],
        self::AFFECTIVE_FIELD_INTEREST => [
            'high' => 'interesting',
            'low' => 'boring'
        ],
        self::AFFECTIVE_FIELD_FAMILIARITY => [
            'high' => 'familiar',
            'low' => 'unfamiliar'
        ],
        self::AFFECTIVE_FIELD_PLAN  => [
            'high' => 'easy to plan',
            'low' => 'unable to plan'
        ],
        self::AFFECTIVE_FIELD_IMPROVEMENT => [
            'high' => 'skills improved',
            'low' => 'skills not improved'
        ],
        self::AFFECTIVE_FIELD_SATISFACTION => [
            'high' => 'triumphant',
            'low' => 'frustrated'
        ]
    ];

    const XY_QUESTIONS = [
        "interest-difficulty" => [
            "text" => "How interesting did you find the task? How difficult did you *personally* find it?",
            "fields" => [self::AFFECTIVE_FIELD_INTEREST, self::AFFECTIVE_FIELD_DIFFICULTY],
        ],
        'planning-familiarity' => [
            "text" => "How easy was it to plan how you'd execute the task? How familiar was the material?",
            "fields" => [self::AFFECTIVE_FIELD_PLAN, self::AFFECTIVE_FIELD_FAMILIARITY],
        ],
        'satisfaction-improvement' => [
            "text" => "How did you feel while executing the task? Do you feel like your skills have improved?",
            "fields" =>  [self::AFFECTIVE_FIELD_SATISFACTION, self::AFFECTIVE_FIELD_IMPROVEMENT],
        ]
    ];

    private $manager;
    private $faker;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // Creates faker for mocking data
        $this->faker = Faker\Factory::create('en_GB');

        $testUsers = $this->loadFunctionalTestUsers();
        $this->printArray('Test Users', $testUsers);

        $testStudent = $testUsers['student'];
        $testInstructor = $testUsers['instructor'];

        // Create students
        $students = $this->loadStudents();
        $this->printArray('Students', $students);
        $allStudents = array_merge($students, [$testStudent]);

        // Create instructors
        $instructors = $this->loadInstructors();
        $allInstructors = array_merge($instructors, [$testInstructor]);
        $this->printArray('Instructors', $allInstructors);

        // Create courses
        $courses = $this->loadCourses();
        $this->printArray('Courses', $courses);

        // Create course instances, assign instructors and students
        $courseInstancesAndEnrolments = $this->loadCourseInstances($courses, $allInstructors, $allStudents);
        $courseInstances = $courseInstancesAndEnrolments['courseInstances'];
        $this->printArray('Course Instances', $courseInstances);

        $enrolements = $courseInstancesAndEnrolments['enrolments'];
        $this->printArray('Enrolments', $enrolements);

        // Create affective fields for XY questions
        $affectiveFields = $this->loadAffectiveFields();
        $this->printArray('Affective Fields', $affectiveFields);

        // Create XY questions
        $xyQuestions = $this->loadXYQuestions($affectiveFields);
        $this->printArray('XY Questions', $xyQuestions);

        // Create lab surveys
        $labSurveys = $this->loadLabSurveys($courseInstances, $xyQuestions);
        $this->printArray('Lab Surveys', $labSurveys);

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

    private function loadFunctionalTestUsers(): array
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

    private function createStudent($guid = null, $email = null): Student
    {
        $student = new Student();
        $guid = $guid ? $guid : $this->faker->unique()->randomNumber(7);
        $student->setGuid($guid);

        // Add to db
        $this->manager->persist($student);

        $this->createUser(function ($user) use ($guid, $email, $student) {
            if (!$email) {
                $firstSurnameLetter = strtolower($user->getSurname()[0]);
                $email = $guid . $firstSurnameLetter . '@student.gla.ac.uk';
            }
            $user->setEmail($email);
            $user->setStudent($student);
        });

        return $student;
    }

    private function loadStudents(): array
    {
        $students = [];

        for ($i = 0; $i < 10; $i++) {
            $students[] = $this->createStudent();
        }

        return $students;
    }

    private function createInstructor($email = null): Instructor
    {
        $instructor = new Instructor();

        // Add to db
        $this->manager->persist($instructor);

        $this->createUser(function ($user) use ($email, $instructor) {
            if (!$email) {
                $email = $user->getForename() . '.' . $user->getSurname() . '@glasgow.ac.uk';
                $email = strtolower($email);
            }
            $user->setEmail($email);
            $user->setInstructor($instructor);
        });

        return $instructor;
    }

    private function loadInstructors(): array
    {
        $instructors = [];

        for ($i = 0; $i < 5; $i++) {
            $instructors[] = $this->createInstructor();
        }

        return $instructors;
    }

    private function createCourse(): Course
    {
        $course = new Course();

        $courseCode = $this->faker->unique()->numerify('COMPSCI####');
        $course->setCode($courseCode);

        $name = sprintf(
            self::COURSE_TITLE_TEMPLATES[array_rand(self::COURSE_TITLE_TEMPLATES)],
            self::COURSE_TITLE_SUBJECTS[array_rand(self::COURSE_TITLE_SUBJECTS)]
        );
        $course->setName($name);

        $description = $this->faker->optional()->text($maxNbChars = 400);
        $course->setDescription($description);

        $this->manager->persist($course);

        return $course;
    }

    private function loadCourses(): array
    {
        $courses = [];

        for ($i = 0; $i < 10; $i++) {
            $courses[] = $this->createCourse();
        }

        return $courses;
    }

    private function createCourseInstance($course, $startDate, $endDate) {
        $courseInstance = new CourseInstance();
        $courseInstance->setCourse($course);
        $courseInstance->setStartDate($startDate);
        $courseInstance->setEndDate($endDate);
        // May  need to change this...
        $this->manager->persist($courseInstance);
        return $courseInstance;
    }

    private function loadCourseInstances(array $courses, array $instructors, array $students): array
    {
        $courseInstances = [];
        foreach ($courses as $course) {

            // Assign a course to one term or both randomly.
            $assigned = false;
            while(!$assigned) {

            if ((bool)rand(0, 1)) {
                $startDate = date_create(self::TERM_DATES['firstTerm']['start']);
                $endDate = date_create(self::TERM_DATES['firstTerm']['end']);
                $courseInstances[] = $this->createCourseInstance($course, $startDate, $endDate);
                $assigned = true;
            }

            if ((bool)rand(0, 1)) {
                $startDate = date_create(self::TERM_DATES['secondTerm']['start']);
                $endDate = date_create(self::TERM_DATES['secondTerm']['end']);
                $courseInstances[] = $this->createCourseInstance($course, $startDate, $endDate);
                $assigned = true;
            }
            }
        }

        // May need to touch this up to ensure that instructors have a minimum of X courses.
        foreach ($courseInstances as $courseInstance) {
            // Randomise instructor assignment order
            $instructorsForInstance = $this->faker->shuffle($instructors);
            // Assign instructors to courses (between 2-4 for each course instance):
            for ($i = 0; $i < rand(2, 4); $i++) {
                $courseInstance->addInstructor($instructorsForInstance[$i]);
            }
        }

        $enrolments = [];

        // Assign students to courses by creating enrolment objects
        foreach ($students as $student) {
            // Randomise course assignment order
            $courseInstancesForStudent = $this->faker->shuffle($courseInstances);

            // Each student is enrolled on 8-10 courses
            // Note: at present this may be the same course for 2 terms in a row. Woo!
            for ($i = 0; $i < rand(8, 10); $i++) {
                $enrolment = new Enrolment();
                $enrolment->setStudent($student);
                $courseInstance = $courseInstancesForStudent[$i];
                $courseInstance->addEnrolment($enrolment);
                $this->manager->persist($enrolment);
                $enrolments[] = $enrolment;
            }
        }

        return [
            'courseInstances' => $courseInstances,
            'enrolments' => $enrolments,
        ];
    }

    public function loadAffectiveFields() : array
    {
        $affectiveFields = [];

        foreach (self::AFFECTIVE_FIELDS as $name => $labels) {
            $affectiveField = new AffectiveField();
            $affectiveField->setName($name);
            foreach ($labels as $position => $label) {
                switch ($position) {
                    case 'high':
                        $affectiveField->setHighLabel($label);
                        break;
                    case 'low':
                        $affectiveField->setLowLabel($label);
                        break;
                    default:
                        throw new Exception("Invalid affective field position", 1);
                        break;
                }
            }
            $this->manager->persist($affectiveField);
            $affectiveFields[$name] = $affectiveField;
        }

        return $affectiveFields;
    }

    public function loadXYQuestions($affectiveFields) : array
    {
        $xyQuestions = [];

        foreach (self::XY_QUESTIONS as $name => $props) {
            $xyQuestion = new XYQuestion();

            $xFieldName = $props['fields'][0];
            $xField = $affectiveFields[$xFieldName];

            $yFieldName = $props['fields'][1];
            $yField = $affectiveFields[$yFieldName];

            $xyQuestion
                ->setName($name)
                ->setQuestionText($props['text'])
                ->setXField($xField)
                ->setYField($yField);

            $this->manager->persist($xyQuestion);

            $xyQuestions[$name] = $xyQuestion;
        }

        return $xyQuestions;
    }

    public function loadLabSurveys(array $courseInstances, array $xyQuestions) : array
    {
        $labSurveys = [];

        foreach ($courseInstances as $courseInstance) {
            // Copy as we don't want to mutate the original
            $courseStart = clone $courseInstance->getStartDate();

            // Set a random day in the week for the lab at random time
            $courseStart
                ->modify('+' . rand(0, 4) . 'day')
                ->setTime(rand(9, 16), 0);

            // Create 10 labs every week for each course
            for ($i = 0; $i < 10; $i++) {
                $labSurvey = new LabSurvey();

                $labStartDate = clone $courseStart;
                $labStartDate->modify('+' . $i . 'week');

                $labSurvey
                    ->setLabName('Lab ' . strval($i + 1))
                    ->setStartDateTime($labStartDate)
                    ->setCourseInstance($courseInstance);

                // Add stock XY Questions for each lab instance
                foreach ($xyQuestions as $xyQuestion) {
                    $labSurveyXYQuestion = new LabSurveyXYQuestion();

                    $labSurveyXYQuestion
                        ->setXyQuestion($xyQuestion)
                        ->setLabSurvey($labSurvey);

                    $this->manager->persist($labSurveyXYQuestion);
                }

                $this->manager->persist($labSurvey);
                $labSurveys[] = $labSurvey;
            }
        }
        return $labSurveys;
    }

    // HELPER FUNCTIONS

    /**
     * Helper function for printing fixtures that have been generated.
     *
     * @param string $title to display above fixtures
     * @param array $array of objects to print
     * @return void
     */
    private function printArray(string $title, array $array)
    {
        printf("%s\n-----\n \n", $title);
        foreach ($array as $str) {
            printf($str);
        }
        printf("\n");
    }
}
