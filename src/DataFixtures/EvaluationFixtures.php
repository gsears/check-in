<?php

/*
AppFixtures.php
Gareth Sears - 2493194S
*/

namespace App\DataFixtures;

use Faker;

use App\Entity\Student;
use App\Entity\Instructor;
use App\Containers\XYCoordinates;
use App\Entity\SentimentQuestion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Containers\Risk\SurveyQuestionResponseRisk;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

/**
 * Populates the database with dummy data for testing and evaluation.
 *
 * Generally it is good Symfony practice to put these in one file so that variables can be passed around,
 * as multiple flushes and database calls can make generation VERY slow.
 */
class EvaluationFixtures extends Fixture implements FixtureGroupInterface
{

    const STUDENT_COUNT = 300;
    const INSTRUCTOR_COUNT = 30;
    const COURSE_COUNT = 5;
    const MIN_COURSE_MEMBERSHIP = 8;
    const MAX_COURSE_MEMBERSHIP = 10;

    const SIMULATED_CURRENT_DATE = "2pm 20 November 2020";

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

    const MOCK_SENTIMENT_RESPONSES = [
        [
            "text" => "So far things have been going ok. I've had a little difficulty with coursework, but mostly I am on top of things and generally happy. Lectures are really fun!",
            "classification" => "Positive",
            "confidence" => 0.96
        ],
        [
            "text" => "I think I'm falling behind. It's really hard and I get errors that I don't understand. The lectures are not useful at all.",
            "classification" => "Negative",
            "confidence" => 0.998
        ],
        [
            "text" => "It's OK. I expected that I'd be learning more but there have been some interesting things.",
            "classification" => "Positive",
            "confidence" => 0.535
        ],
        [
            "text" => "I've been a bit swamped with my part time job and am really stressed right now. The tutors are not supporting me at all, they're always talking to the same students who ask questions all the time. I feel left out.",
            "classification" => "Negative",
            "confidence" => 0.985
        ],
        [
            "text" => "It's fine I guess.",
            "classification" => "Neutral",
            "confidence" => 0.466
        ],
    ];

    private $faker;
    private $creator;
    private $projectDirectory;

    /**
     * Dependency inject the root directory via services.yaml config
     */
    public function __construct(string $projectDirectory)
    {
        $this->projectDirectory = $projectDirectory;
    }

    /**
     * Returns the group for this fixture, which allows selective loading (e.g 'app' fixtures vs 'test' fixtures)
     *
     * @return array
     */
    public static function getGroups(): array
    {
        return ['evaluation'];
    }

    public function load(ObjectManager $manager)
    {
        // Turn off auto-flushing with the entity creator as this can be slow.
        $this->creator = new EntityCreator($manager);
        $this->creator->setAutoflush(false);

        // Creates faker for mocking data
        $this->faker = Faker\Factory::create('en_GB');

        $testStudent = $this->loadStudentTestAccount();
        $testInstructor = $this->loadInstructorTestAccount();

        printf("Loaded test users\n");

        // Create students
        $students = $this->loadStudents();
        $allStudents = array_merge($students, [$testStudent]);

        // List of student usernames for evaluation testing
        $this->outputUsernamesCSV($students, 'docs/evaluation/students.csv');

        printf("Loaded students\n");

        // Create instructors
        $instructors = $this->loadInstructors();
        $allInstructors = array_merge($instructors, [$testInstructor]);

        // For of instructor usernames for evaluation testing
        $this->outputUsernamesCSV($instructors, 'docs/evaluation/instructors.csv');

        printf("Loaded instructors\n");

        // Create courses
        $courses = $this->loadCourses();

        printf("Loaded courses\n");

        // Create course instances, assign instructors and students. Return wrappers
        // ['courseInstance' => ... , 'students' => [...], 'instructors' => [...]]
        $courseInstanceWrappers = $this->loadCourseInstances(
            $courses,
            $allInstructors,
            $allStudents,
            $testStudent,
            $testInstructor
        );

        printf("Loaded course instances\n");

        // Create affective fields for XY questions
        $affectiveFields = $this->loadAffectiveFields();

        printf("Loaded affective fields\n");

        // Create XY questions
        $xyQuestions = $this->loadXYQuestions($affectiveFields);

        printf("Loaded xy questions\n");

        // Create Sentiment questions
        $sentimentQuestions = $this->loadSentimentQuestions();

        printf("Loaded sentiment questions\n");

        // Create lab surveys and populate responses
        $labs = $this->loadLabs($courseInstanceWrappers, $xyQuestions, $sentimentQuestions);

        printf("Loaded labs ...flushing...\n");

        $manager->flush();

        printf("Flush complete\n");

        $manager->clear();
    }

    private function loadStudentTestAccount(): Student
    {
        // Passwords default to 'password'
        $testStudentGuid = $this->faker->unique()->passthrough(1234567);

        return $this->creator->createStudent(
            $this->faker->firstName(),
            $this->faker->lastName(),
            $testStudentGuid,
            Config::TEST_STUDENT_EMAIL
        );
    }

    private function loadInstructorTestAccount(): Instructor
    {
        return $this->creator->createInstructor(
            $this->faker->firstName(),
            $this->faker->lastName(),
            Config::TEST_INSTUCTOR_EMAIL
        );
    }

    private function loadStudents(): array
    {
        $students = [];

        for ($i = 0; $i < self::STUDENT_COUNT; $i++) {
            $student = $this->creator->createStudent(
                $this->faker->firstName(),
                $this->faker->lastName(),
                $this->faker->unique()->randomNumber(7)
            );

            $students[] = $student;
        }

        return $students;
    }

    private function loadInstructors(): array
    {
        $instructors = [];

        for ($i = 0; $i < self::INSTRUCTOR_COUNT; $i++) {
            $instructor = $this->creator->createInstructor(
                $this->faker->firstName(),
                $this->faker->lastName()
            );
            $instructors[] = $instructor;
        }

        return $instructors;
    }

    private function loadCourses(): array
    {
        $courses = [];

        for ($i = 0; $i < self::COURSE_COUNT; $i++) {
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

    private function loadCourseInstances(array $courses, array $instructors, array $students, Student $testStudent, Instructor $testInstructor): array
    {
        $courseInstanceWrappers = [];

        // Create a course instance of a course in both terms
        foreach ($courses as $course) {
            $courseIndex = 1;
            foreach (Config::TERM_DATES as $term) {
                $startDate = date_create($term['start']);
                $endDate = date_create($term['end']);

                // Wrappers used to hold members in memory so we don't need to
                // query the database when making fixtures, which makes things
                // very slow.
                $courseInstanceWrappers[] = [
                    'courseInstance' => $this->creator->createCourseInstance(
                        $course,
                        $startDate,
                        $endDate,
                        $courseIndex // Manually set course index to prevent db query.
                    ),
                    'students' => [],
                    'instructors' => [],
                ];

                $courseIndex++;
            }
        }

        // Randomly assign courses to instructors.
        foreach ($instructors as $instructor) {

            shuffle($courseInstanceWrappers); // Randomise course assignment order

            for ($i = 0; $i < rand(self::MIN_COURSE_MEMBERSHIP, self::MAX_COURSE_MEMBERSHIP); $i++) {
                $courseInstanceWrapper = $courseInstanceWrappers[$i];
                $instructor->addCourseInstance($courseInstanceWrapper['courseInstance']);
                // Push instructor onto that course instance wrapper
                $courseInstanceWrapper['instructors'][] = $instructor;
                // Update wrapper in course instance array
                $courseInstanceWrappers[$i] = $courseInstanceWrapper;
            }
        }

        // Randomly assign courses to students.
        foreach ($students as $student) {
            // Randomise course assignment order
            shuffle($courseInstanceWrappers);
            // Note: at present this may be the same course for 2 terms in a row. Woo!
            for ($i = 0; $i < rand(self::MIN_COURSE_MEMBERSHIP, self::MAX_COURSE_MEMBERSHIP); $i++) {
                $courseInstanceWrapper = $courseInstanceWrappers[$i];
                $courseInstance = $courseInstanceWrapper['courseInstance'];

                $this->creator->createEnrolment(
                    $student,
                    $courseInstance
                );

                // Ensure the test user is always taught by the test instructor for manual testing
                if ($student === $testStudent) {
                    $courseInstance->addInstructor($testInstructor);
                }
                // Push student onto that course instance wrapper
                $courseInstanceWrapper['students'][] = $student;
                // Update wrapper in courseInstance array
                $courseInstanceWrappers[$i] = $courseInstanceWrapper;
            }
        }

        return $courseInstanceWrappers;
    }

    private function loadAffectiveFields(): array
    {
        $affectiveFields = [];

        foreach (Config::AFFECTIVE_FIELDS as $name => $labels) {
            $affectiveField = $this->creator->createAffectiveField(
                $name,
                $labels['low'],
                $labels['high']
            );

            $affectiveFields[$name] = $affectiveField;
        }

        return $affectiveFields;
    }

    private function loadXYQuestions($affectiveFields): array
    {
        $xyQuestions = [];

        foreach (Config::XY_QUESTIONS as $name => $props) {
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

    private function loadSentimentQuestions(): array
    {
        $sentimentQuestions = [];

        foreach (Config::SENTIMENT_QUESTIONS as $name => $props) {
            $sentimentQuestion = $this->creator->createSentimentQuestion(
                $name,
                $props['text'],
            );

            $sentimentQuestions[$name] = $sentimentQuestion;
        }

        return $sentimentQuestions;
    }

    private function loadLabs(array $courseInstanceWrappers, array $xyQuestions, array $sentimentQuestions): array
    {
        $labs = [];

        foreach ($courseInstanceWrappers as $courseInstanceWrapper) {
            $courseInstance = $courseInstanceWrapper['courseInstance'];
            // Copy as we don't want to mutate the original
            $courseStart = clone $courseInstance->getStartDate();
            // Set a random day in the week for the lab at random time
            $courseStart->modify('+' . rand(0, 4) . 'day')->setTime(rand(9, 16), 0);

            // Create 10 labs (every week) for each course
            for ($i = 0; $i < 10; $i++) {

                $labStartDate = clone $courseStart;
                $labStartDate->modify('+' . $i . 'week');

                $lab = $this->creator->createLab(
                    'Lab ' . strval($i + 1),
                    $labStartDate,
                    $courseInstance
                );

                // Add stock XY Questions for each lab instance
                $labXYQuestions = [];

                foreach ($xyQuestions as $name => $xyQuestion) {
                    $labXYQuestion = $this->creator->createLabXYQuestion(
                        Config::XY_QUESTIONS[$name]['index'],
                        $xyQuestion,
                        $lab
                    );

                    // Add initial danger zones and warning zones
                    $this->creator->createLabXYQuestionDangerZone(
                        SurveyQuestionResponseRisk::LEVEL_DANGER,
                        -10,
                        -6,
                        -10,
                        -6,
                        $labXYQuestion
                    );

                    $this->creator->createLabXYQuestionDangerZone(
                        SurveyQuestionResponseRisk::LEVEL_WARNING,
                        -10,
                        -6,
                        -5,
                        -1,
                        $labXYQuestion
                    );

                    $this->creator->createLabXYQuestionDangerZone(
                        SurveyQuestionResponseRisk::LEVEL_WARNING,
                        -5,
                        -1,
                        -10,
                        -6,
                        $labXYQuestion
                    );

                    $labXYQuestions[] = $labXYQuestion;
                }

                // Add stock sentiment questions to each lab instance
                $labSentimentQuestions = [];

                foreach ($sentimentQuestions as $name => $sentimentQuestion) {
                    $labSentimentQuestion = $this->creator->createLabSentimentQuestion(
                        Config::SENTIMENT_QUESTIONS[$name]['index'],
                        $sentimentQuestion,
                        $lab
                    );

                    // Add initial danger zones and warning zones
                    $this->creator->createLabSentimentQuestionDangerZone(
                        SurveyQuestionResponseRisk::LEVEL_DANGER,
                        SentimentQuestion::NEGATIVE,
                        0.5,
                        1.0,
                        $labSentimentQuestion
                    );

                    $this->creator->createLabSentimentQuestionDangerZone(
                        SurveyQuestionResponseRisk::LEVEL_WARNING,
                        SentimentQuestion::NEGATIVE,
                        0.0,
                        0.5,
                        $labSentimentQuestion
                    );

                    $labSentimentQuestions[] = $labSentimentQuestion;
                }

                // For each student in the lab, generate a lab response object, either empty
                // or populated with some values.
                $studentsInLab = $courseInstanceWrapper['students'];
                $cutoffDate = date_create(self::SIMULATED_CURRENT_DATE);

                foreach ($studentsInLab as $student) {

                    // Create an empty lab response for each student
                    $labResponse = $this->creator->createLabResponse(
                        false,
                        $student,
                        $lab
                    );

                    // No responses before cutoff date. 70% chance of completing a survey.
                    if ($labStartDate < $cutoffDate && rand(0, 10) < 7) {
                        $this->populateLabResponse(
                            $labResponse,
                            $labXYQuestions,
                            $labSentimentQuestions,
                            90,  // 90% chance of completing a question.
                        );
                    }
                }

                $labs[] = $lab;
            }
        }

        return $labs;
    }

    private function populateLabResponse(
        $labResponse,
        $labXYQuestions,
        $labSentimentQuestions,
        $probabilityCompleteQuestion
    ) {

        $labResponse->setSubmitted(true);

        foreach ($labXYQuestions as $labXYQuestion) {
            if (rand(0, 100) < $probabilityCompleteQuestion) {

                // Randomise X and Y selections
                $xVal = rand(-10, 9);
                $yVal = rand(-10, 9);

                $this->creator->createLabXYQuestionResponse(
                    new XYCoordinates($xVal, $yVal),
                    $labXYQuestion,
                    $labResponse
                );
            }
        }

        foreach ($labSentimentQuestions as $labSentimentQuestion) {
            if (rand(0, 100) < $probabilityCompleteQuestion) {

                $mock = self::MOCK_SENTIMENT_RESPONSES[array_rand(self::MOCK_SENTIMENT_RESPONSES, 1)];

                $this->creator->createLabSentimentQuestionResponse(
                    $mock['text'],
                    $mock['classification'],
                    $mock['confidence'],
                    $labSentimentQuestion,
                    $labResponse
                );
            }
        }

        return $labResponse;
    }

    private function outputUsernamesCSV(array $userTypes, string $filename)
    {
        $newCSV = fopen($this->projectDirectory . '/' . $filename, 'w');

        foreach ($userTypes as $userType) {
            $user = $userType->getUser();
            fputcsv($newCSV, [$user->getEmail()]);
        }

        fclose($newCSV);
    }
}
