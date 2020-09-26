<?php

/*
TestFixtures.php
Gareth Sears - 2493194S
*/

namespace App\DataFixtures;

use App\Entity\Student;
use App\Entity\SentimentQuestion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Containers\Risk\SurveyQuestionResponseRisk;
use App\Containers\XYCoordinates;
use App\Entity\LabResponse;
use App\Entity\LabXYQuestion;
use App\Repository\LabXYQuestionRepository;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

/**
 * Populates the database with dummy data for testing and evaluation with the checklist
 * included in the zip file.
 *
 * Generally it is good Symfony practice to put these in one file so that variables can be passed around.
 */
class TestFixtures extends Fixture implements FixtureGroupInterface
{
    private $manager;
    private $creator;

    /**
     * Returns the group for this fixture, which allows selective loading (e.g 'app' fixtures vs 'test' fixtures)
     *
     * @return array
     */
    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager)
    {
        $creator = new EntityCreator($manager);
        $creator->setAutoflush(false); // Turn off autoflush for speed.

        // Share between methods
        $this->creator = $creator;
        $this->manager = $manager;

        $studentUser = $creator->createStudent(
            "Student",
            "Test",
            "1234567",
            "test@student.gla.ac.uk"
        );

        $studentCS101Term1 = $creator->createStudent(
            "Student",
            "CS101Term1",
            "2345678",
        );

        $studentCS101Term2 = $creator->createStudent(
            "Student",
            "CS101Term2",
            "3456789",
        );

        $instructorUser = $creator->createInstructor(
            "Instructor",
            "Test",
            "test@glasgow.ac.uk"
        );

        $instructorCOMPSCI101 = $creator->createInstructor(
            "Instructor",
            "COMPSCI101",
            "i1@test.com"
        );

        $instructorCOMPSCI202 = $creator->createInstructor(
            "Instructor",
            "COMPSCI202",
            "i2@test.com"
        );

        $courseCOMPSCI101 = $creator->createCourse(
            'COMPSCI101',
            'Course One',
            'Course One Description'
        );

        $courseCOMPSCI202 = $creator->createCourse(
            'COMPSCI202',
            'Course Two',
            'Course Two Description'
        );

        $firstTermStart = date_create(Config::TERM_DATES['firstTerm']['start']);
        $firstTermEnd = date_create(Config::TERM_DATES['firstTerm']['end']);
        $secondTermStart =  date_create(Config::TERM_DATES['secondTerm']['start']);
        $secondTermEnd = date_create(Config::TERM_DATES['secondTerm']['end']);

        $courseInstanceCOMPSCI101Term1 = $creator->createCourseInstance(
            $courseCOMPSCI101,
            $firstTermStart,
            $firstTermEnd,
            1
        );

        $courseInstanceCOMPSCI101Term2 = $creator->createCourseInstance(
            $courseCOMPSCI101,
            $secondTermStart,
            $secondTermEnd,
            2
        );

        $courseInstanceCOMPSCI202Term1 = $creator->createCourseInstance(
            $courseCOMPSCI202,
            $firstTermStart,
            $firstTermEnd,
            1
        );

        $courseInstanceCOMPSCI202Term2 = $creator->createCourseInstance(
            $courseCOMPSCI202,
            $secondTermStart,
            $secondTermEnd,
            2
        );

        $courseInstances = [
            $courseInstanceCOMPSCI101Term1,
            $courseInstanceCOMPSCI101Term2,
            $courseInstanceCOMPSCI202Term1,
            $courseInstanceCOMPSCI202Term2,
        ];

        // Enrol test student on COMPSCI101-1 and COMPSCI202-2
        $creator->createEnrolment(
            $studentUser,
            $courseInstanceCOMPSCI101Term1
        );

        $creator->createEnrolment(
            $studentUser,
            $courseInstanceCOMPSCI202Term2
        );

        $creator->createEnrolment(
            $studentCS101Term1,
            $courseInstanceCOMPSCI101Term1
        );

        // Enrol other students on respective courses
        $creator->createEnrolment(
            $studentCS101Term2,
            $courseInstanceCOMPSCI101Term2
        );

        // User Instructor teaches COMPSCI101-1 and COMPSCI202-2
        $courseInstanceCOMPSCI101Term1->addInstructor($instructorUser);
        $courseInstanceCOMPSCI202Term2->addInstructor($instructorUser);

        // Assign other instructors
        $courseInstanceCOMPSCI101Term1->addInstructor($instructorCOMPSCI101);
        $courseInstanceCOMPSCI101Term2->addInstructor($instructorCOMPSCI101);
        $courseInstanceCOMPSCI202Term1->addInstructor($instructorCOMPSCI202);
        $courseInstanceCOMPSCI202Term2->addInstructor($instructorCOMPSCI202);

        // Load affective fields
        $affectiveFields = $this->loadAffectiveFields();
        $xyQuestions = $this->loadXYQuestions($affectiveFields);
        $sentimentQuestions = $this->loadSentimentQuestions();

        // Flush here so that loadLabs can make database queries to find
        // students per course.

        $manager->flush();

        $this->loadLabs($courseInstances, $xyQuestions, $sentimentQuestions);

        // Flush so query can be made against lab responses
        $manager->flush();

        // First lab completed by user for CS101-1
        $labResponsesForUserInCS101Term1 = $this->manager
            ->getRepository(LabResponse::class)
            ->findByCourseInstanceAndStudent(
                $courseInstanceCOMPSCI101Term1,
                $studentUser
            );

        $userFirstLabResponseCS101Term1 = $labResponsesForUserInCS101Term1[0];

        // Create responses for XY Questions
        $firstLabCS101Term1 = $userFirstLabResponseCS101Term1->getLab();

        $firstLabXYQuestions = $this->manager
            ->getRepository(LabXYQuestion::class)
            ->findBy(['lab' => $firstLabCS101Term1], ['index' => 'ASC']);

        // $firstLabCS101Term1->getlabXYQuestions();

        $creator->createLabXYQuestionResponse(
            new XYCoordinates(-10, -10),
            $firstLabXYQuestions[0],
            $userFirstLabResponseCS101Term1
        );

        $creator->createLabXYQuestionResponse(
            new XYCoordinates(0, 0),
            $firstLabXYQuestions[1],
            $userFirstLabResponseCS101Term1
        );

        $creator->createLabXYQuestionResponse(
            new XYCoordinates(9, 9),
            $firstLabXYQuestions[2],
            $userFirstLabResponseCS101Term1
        );

        $userFirstLabResponseCS101Term1->setSubmitted(true);

        // First lab also completed by student CS101
        $labResponsesForNonUserInCS101Term1 = $this->manager
            ->getRepository(LabResponse::class)
            ->findByCourseInstanceAndStudent(
                $courseInstanceCOMPSCI101Term1,
                $studentCS101Term1
            );

        $nonUserFirstLabResponseCS101Term1 = $labResponsesForNonUserInCS101Term1[0];

        $creator->createLabXYQuestionResponse(
            new XYCoordinates(-9, -9),
            $firstLabXYQuestions[0],
            $nonUserFirstLabResponseCS101Term1
        );

        $creator->createLabXYQuestionResponse(
            new XYCoordinates(0, 0),
            $firstLabXYQuestions[1],
            $nonUserFirstLabResponseCS101Term1
        );

        $creator->createLabXYQuestionResponse(
            new XYCoordinates(-10, -10),
            $firstLabXYQuestions[2],
            $nonUserFirstLabResponseCS101Term1
        );

        $nonUserFirstLabResponseCS101Term1->setSubmitted(true);

        $manager->flush();
        $manager->clear();
    }

    public function loadAffectiveFields(): array
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

    public function loadXYQuestions($affectiveFields): array
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

    public function loadSentimentQuestions(): array
    {
        $sentimentQuestions = [];

        foreach (Config::SENTIMENT_QUESTIONS as $name => $props) {
            $sentimentQuestion = $this->creator->createSentimentQuestion(
                $name,
                $props['text']
            );

            $sentimentQuestions[$name] = $sentimentQuestion;
        }

        return $sentimentQuestions;
    }

    public function loadLabs(array $courseInstances, array $xyQuestions, array $sentimentQuestions): void
    {
        $secondTermStart = date_create(Config::TERM_DATES['secondTerm']['start']);

        foreach ($courseInstances as $courseInstance) {
            if ($courseInstance->getStartDate() < $secondTermStart) {
                // Set specific first term date to start labs for
                $firstLabDate = date_create("12 November 2020");
            } else {
                $firstLabDate = date_create(Config::TERM_DATES['secondTerm']['start']);
            }

            // Create 10 labs every day from start date.
            for ($i = 0; $i < 10; $i++) {
                // Clone to avoid mutations.
                $labStartDate = clone $firstLabDate;
                $labStartDate->modify("+" . $i . " day");

                // Alternate times
                if ($i % 2 === 0) {
                    $labStartDate->setTime(13, 0);
                } else {
                    $labStartDate->setTime(9, 0);
                }

                $lab = $this->creator->createLab(
                    'Lab ' . strval($i + 1),
                    $labStartDate,
                    $courseInstance
                );

                // Add stock XY Questions for each lab instance
                foreach ($xyQuestions as $name => $xyQuestion) {
                    $labXYQuestion = $this->creator->createLabXYQuestion(
                        Config::XY_QUESTIONS[$name]['index'],
                        $xyQuestion,
                        $lab
                    );

                    // With one basic danger zone as default
                    $dangerZone = $this->creator->createLabXYQuestionDangerZone(
                        SurveyQuestionResponseRisk::LEVEL_DANGER,
                        -10,
                        -6,
                        -10,
                        -6,
                        $labXYQuestion
                    );

                    // And initial warning zones
                    $warningZone = $this->creator->createLabXYQuestionDangerZone(
                        SurveyQuestionResponseRisk::LEVEL_WARNING,
                        -10,
                        -6,
                        -5,
                        -1,
                        $labXYQuestion
                    );

                    $warningZone = $this->creator->createLabXYQuestionDangerZone(
                        SurveyQuestionResponseRisk::LEVEL_WARNING,
                        -5,
                        -1,
                        -10,
                        -6,
                        $labXYQuestion
                    );
                }

                // Add stock sentiment questions to each lab instance
                foreach ($sentimentQuestions as $name => $sentimentQuestion) {
                    $labSentimentQuestion = $this->creator->createLabSentimentQuestion(
                        Config::SENTIMENT_QUESTIONS[$name]['index'],
                        $sentimentQuestion,
                        $lab
                    );

                    // Warning zones as below
                    $dangerZone = $this->creator->createLabSentimentQuestionDangerZone(
                        SurveyQuestionResponseRisk::LEVEL_DANGER,
                        SentimentQuestion::NEGATIVE,
                        0.5,
                        1.0,
                        $labSentimentQuestion
                    );

                    // Warning zones as below
                    $warningZone = $this->creator->createLabSentimentQuestionDangerZone(
                        SurveyQuestionResponseRisk::LEVEL_WARNING,
                        SentimentQuestion::NEGATIVE,
                        0.0,
                        0.5,
                        $labSentimentQuestion
                    );
                }

                // For each student in the lab, generate an empty response object
                $studentsInLab = $this->manager
                    ->getRepository(Student::class)
                    ->findByCourseInstance($courseInstance);

                foreach ($studentsInLab as $student) {
                    $labResponse = $this->creator->createLabResponse(
                        false,
                        $student,
                        $lab
                    );
                }
            }
        }
    }
}
