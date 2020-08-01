<?php

namespace App\DataFixtures;

use App\Entity\Lab;
use Faker;
use App\Entity\LabResponse;
use App\Entity\LabXYQuestion;
use App\Entity\Student;
use App\Entity\XYCoordinates;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

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

    const DEFAULT_RISK_THRESHOLD_PERCENT =  70;
    const DEFAULT_RISK_CONSECUTIVE_LABS = 2;

    const SIMULATED_CURRENT_DATE = "2pm 20 November 2020";

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
            "index" => 1,
            "text" => "How interesting did you find the task? How difficult did you *personally* find it?",
            "fields" => [self::AFFECTIVE_FIELD_INTEREST, self::AFFECTIVE_FIELD_DIFFICULTY],
        ],
        'planning-familiarity' => [
            "index" => 2,
            "text" => "How easy was it to plan how you'd execute the task? How familiar was the material?",
            "fields" => [self::AFFECTIVE_FIELD_PLAN, self::AFFECTIVE_FIELD_FAMILIARITY],
        ],
        'satisfaction-improvement' => [
            "index" => 3,
            "text" => "How did you feel while executing the task? Do you feel like your skills have improved?",
            "fields" =>  [self::AFFECTIVE_FIELD_SATISFACTION, self::AFFECTIVE_FIELD_IMPROVEMENT],
        ]
    ];

    private $manager;
    private $faker;
    private $creator;

    public function load(ObjectManager $manager)
    {

        $this->manager = $manager;
        $this->creator = new EntityCreator($manager);

        // Creates faker for mocking data
        $this->faker = Faker\Factory::create('en_GB');

        $testUsers = $this->loadFunctionalTestUsers();
        $this->printArray('Test Users', $testUsers);

        $testStudent = $testUsers['student'];
        $testInstructor = $testUsers['instructor'];

        // Create students
        $students = $this->loadStudents();
        $allStudents = array_merge($students, [$testStudent]);

        // Create instructors
        $instructors = $this->loadInstructors();
        $allInstructors = array_merge($instructors, [$testInstructor]);

        // Create courses
        $courses = $this->loadCourses();

        // Create course instances, assign instructors and students
        $courseInstancesAndEnrolments = $this->loadCourseInstances($courses, $allInstructors, $allStudents);
        $courseInstances = $courseInstancesAndEnrolments['courseInstances'];

        $enrolements = $courseInstancesAndEnrolments['enrolments'];

        // Create affective fields for XY questions
        $affectiveFields = $this->loadAffectiveFields();

        // Create XY questions
        $xyQuestions = $this->loadXYQuestions($affectiveFields);

        // Create lab surveys
        $labs = $this->loadLabs($courseInstances, $xyQuestions);

        // Create lab xy responses
        $completedLabResponses = $this->loadLabResponses($courseInstances);

        $manager->clear();
    }

    private function loadFunctionalTestUsers(): array
    {
        $testStudentGuid = $this->faker->unique()->passthrough(1234567);
        $testStudentEmail = self::TEST_STUDENT_USERNAME;
        $testStudent = $this->creator->createStudent(
            "Gareth",
            "Sears",
            $testStudentGuid,
            $testStudentEmail
        );

        $testInstructorEmail = self::TEST_INSTUCTOR_USERNAME;
        $testInstructor = $this->creator->createInstructor(
            $this->faker->firstName(),
            $this->faker->lastName(),
            $testInstructorEmail
        );

        return [
            'student' => $testStudent,
            'instructor' => $testInstructor
        ];
    }

    private function loadStudents(): array
    {
        $students = [];

        for ($i = 0; $i < 1; $i++) {
            $students[] = $this->creator->createStudent(
                $this->faker->firstName(),
                $this->faker->lastName(),
                $this->faker->unique()->randomNumber(7)
            );
        }

        return $students;
    }

    private function loadInstructors(): array
    {
        $instructors = [];

        for ($i = 0; $i < 1; $i++) {
            $instructors[] = $this->creator->createInstructor(
                $this->faker->firstName(),
                $this->faker->lastName()
            );
        }

        return $instructors;
    }


    private function loadCourses(): array
    {
        $courses = [];

        for ($i = 0; $i < 3; $i++) {
            $name = sprintf(
                self::COURSE_TITLE_TEMPLATES[array_rand(self::COURSE_TITLE_TEMPLATES)],
                self::COURSE_TITLE_SUBJECTS[array_rand(self::COURSE_TITLE_SUBJECTS)]
            );

            $courses[] = $this->creator->createCourse(
                $this->faker->unique()->numerify('COMPSCI####'),
                $name,
                $this->faker->optional()->text($maxNbChars = 400)
            );
        }

        return $courses;
    }

    private function loadCourseInstances(array $courses, array $instructors, array $students): array
    {
        $courseInstances = [];

        foreach ($courses as $course) {

            $assigned = false; // Assign a course to one term or both randomly.

            while (!$assigned) {

                if ((bool)rand(0, 1)) {
                    $startDate = date_create(self::TERM_DATES['firstTerm']['start']);
                    $endDate = date_create(self::TERM_DATES['firstTerm']['end']);
                    $courseInstances[] = $this->creator->createCourseInstance(
                        $course,
                        $startDate,
                        $endDate,
                        self::DEFAULT_RISK_THRESHOLD_PERCENT,
                        self::DEFAULT_RISK_CONSECUTIVE_LABS
                    );
                    $assigned = true;
                }

                if ((bool)rand(0, 1)) {
                    $startDate = date_create(self::TERM_DATES['secondTerm']['start']);
                    $endDate = date_create(self::TERM_DATES['secondTerm']['end']);
                    $courseInstances[] = $this->creator->createCourseInstance(
                        $course,
                        $startDate,
                        $endDate,
                        self::DEFAULT_RISK_THRESHOLD_PERCENT,
                        self::DEFAULT_RISK_CONSECUTIVE_LABS
                    );
                    $assigned = true;
                }
            }
        }

        foreach ($instructors as $instructor) {
            // Randomise course assignment order
            $coursesForInstructors = $this->faker->shuffle($courseInstances);
            // Assign courses to instructors (between 6-8 for each instructor):
            for ($i = 0; $i < rand(2, 3); $i++) {
                $instructor->addCourseInstance($coursesForInstructors[$i]);
            }
        }

        $enrolments = [];

        // Assign students to courses by creating enrolment objects
        foreach ($students as $student) {

            $coursesForStudents = $this->faker->shuffle($courseInstances);

            // Each student is enrolled on 8-10 courses
            // Note: at present this may be the same course for 2 terms in a row. Woo!
            for ($i = 0; $i < rand(2, 3); $i++) {
                $enrolment = $this->creator->createEnrolment(
                    $student,
                    $coursesForStudents[$i]
                );
                $enrolments[] = $enrolment;
            }
        }

        return [
            'courseInstances' => $courseInstances,
            'enrolments' => $enrolments,
        ];
    }

    public function loadAffectiveFields(): array
    {
        $affectiveFields = [];

        foreach (self::AFFECTIVE_FIELDS as $name => $labels) {
            $affectiveField = $this->creator->createAffectiveField(
                $name,
                $labels['low'],
                $labels['high']
            );

            $affectiveFields[$name] = $affectiveField;
        }

        return $affectiveFields;
    }

    public function loadXYQuestions($affectiveFields): array
    {
        $xyQuestions = [];

        foreach (self::XY_QUESTIONS as $name => $props) {
            $xyQuestion = $this->creator->createXYQuestion(
                $name,
                $props['text'],
                $affectiveFields[$props['fields'][0]],
                $affectiveFields[$props['fields'][1]]
            );

            $xyQuestions[$name] = $xyQuestion;
        }

        return $xyQuestions;
    }

    public function loadLabs(array $courseInstances, array $xyQuestions): array
    {
        $labs = [];

        foreach ($courseInstances as $courseInstance) {
            // Copy as we don't want to mutate the original
            $courseStart = clone $courseInstance->getStartDate();

            // Set a random day in the week for the lab at random time
            $courseStart
                ->modify('+' . rand(0, 4) . 'day')
                ->setTime(rand(9, 16), 0);

            // Create 10 labs every week for each course
            for ($i = 0; $i < 10; $i++) {

                $labStartDate = clone $courseStart;
                $labStartDate->modify('+' . $i . 'week');

                $lab = $this->creator->createLab(
                    'Lab ' . strval($i + 1),
                    $labStartDate,
                    $courseInstance
                );

                // Add stock XY Questions for each lab instance
                foreach ($xyQuestions as $name => $xyQuestion) {
                    $labXYQuestion = $this->creator->createLabXYQuestion(
                        self::XY_QUESTIONS[$name]['index'],
                        $xyQuestion,
                        $lab
                    );

                    // With one basic danger zone as default
                    $dangerZone = $this->creator->createLabXYQuestionDangerZone(
                        2,
                        -10,
                        -6,
                        -10,
                        -6,
                        $labXYQuestion
                    );
                }

                // For each student in the lab, generate an empty response object
                $studentsInLab = $this->manager->getRepository(Student::class)
                    ->findByCourseInstance($courseInstance);

                foreach ($studentsInLab as $student) {
                    $labResponse = $this->creator->createLabResponse(
                        false,
                        $student,
                        $lab
                    );
                }

                $labs[] = $lab;
            }
        }

        return $labs;
    }

    public function loadLabResponses($courseInstances)
    {
        $completedResponses = [];

        $cutoffDate = date_create(self::SIMULATED_CURRENT_DATE);

        foreach ($courseInstances as $courseInstance) {
            // Get all enrolled students on that course
            $studentsInCourse = $this->manager->getRepository(Student::class)
                ->findByCourseInstance($courseInstance);

            // For each lab before the cutoff date / time...
            $labs = $this->manager->getRepository(Lab::class)
                ->findByCourseInstance($courseInstance, $cutoffDate);

            foreach ($labs as $lab) {
                // each student...
                foreach ($studentsInCourse as $student) {
                    // 80% chance of completing survey
                    if (rand(0, 10) < 8) {
                        $labResponse = $this->manager->getRepository(LabResponse::class)
                            ->findOneByLabAndStudent($lab, $student);

                        $labResponse->setSubmitted(true);

                        // XYQuestions
                        $labXYQuestions = $this->manager->getRepository(LabXYQuestion::class)
                            ->findBy(['lab' => $lab]);

                        // 90% chance of completing an XY question
                        foreach ($labXYQuestions as $labXYQuestion) {
                            if (rand(0, 10) < 9) {
                                $xyResponse = $this->creator->createLabXYQuestionResponse(
                                    new XYCoordinates(rand(-10, 9), rand(-10, 9)),
                                    $labXYQuestion,
                                    $labResponse
                                );
                            }
                        }
                        $completedResponses[] = $labResponse;
                    }
                }
            }
        }
        return $completedResponses;
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
