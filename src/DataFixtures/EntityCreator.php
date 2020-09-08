<?php

/*
EntityCreator.php
Gareth Sears - 2493194S
*/

namespace App\DataFixtures;

/*
EntityCreator.php
Gareth Sears - 2493194S
*/

use DateTime;
use App\Entity\Lab;
use App\Entity\User;
use App\Entity\Course;
use App\Entity\Student;
use App\Containers\Bound;
use App\Entity\Enrolment;
use App\Entity\Instructor;
use App\Entity\XYQuestion;
use App\Entity\LabResponse;
use App\Entity\LabXYQuestion;
use App\Entity\AffectiveField;
use App\Entity\CourseInstance;
use App\Containers\CourseDates;
use App\Containers\XYCoordinates;
use App\Entity\LabSentimentQuestion;
use App\Entity\LabSentimentQuestionResponse;
use App\Entity\LabXYQuestionResponse;
use App\Entity\LabXYQuestionDangerZone;
use App\Entity\SentimentQuestion;
use App\Entity\LabSentimentQuestionDangerZone;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Helpers to create entities easily.
 */
final class EntityCreator
{
    // 'password', pre-hashed for spee
    const PASSWORD = '$2y$13$M5wzRvyIISgDAjtGJEna5.mnz5QAgsoExUOPEwacu/cOFSz861fjC';

    private $em;
    private $autoflush = true;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function setAutoflush(bool $autoflush)
    {
        $this->autoflush = $autoflush;
    }

    private function save($entity)
    {
        $this->em->persist($entity);
        if ($this->autoflush) {
            $this->em->flush();
        }

        return $entity;
    }

    public function createUser(string $forename, string $surname, string $email, string $password): User
    {
        $user = (new User())
            ->setForename($forename)
            ->setSurname($surname)
            ->setPassword($password)
            ->setEmail($email);

        return $this->save($user);
    }

    public function createStudent(string $forename, string $surname, string $guid, string $email = null, string $password = self::PASSWORD): Student
    {
        if (!$email) {
            $firstSurnameLetter = strtolower($surname[0]);
            $email = $guid . $firstSurnameLetter . '@student.gla.ac.uk';
        }

        $user = $this->createUser(
            $forename,
            $surname,
            $email,
            $password
        );

        $student = (new Student())
            ->setGuid($guid);

        $user->setStudent($student);

        return $this->save($student);
    }

    public function createInstructor(string $forename, string $surname, string $email = null,  string $password = self::PASSWORD): Instructor
    {
        if (!$email) {
            $email = strtolower($forename . '.' . $surname . '@glasgow.ac.uk');
        }

        $user = $this->createUser(
            $forename,
            $surname,
            $email,
            $password
        );

        $instructor = new Instructor();
        $user->setInstructor($instructor);

        return $this->save($instructor);
    }

    public function createCourse(string $courseCode, string $name, ?string $description): Course
    {
        $course = (new Course())
            ->setCode($courseCode)
            ->setName($name)
            ->setDescription($description);

        return $this->save($course);
    }

    public function createCourseInstance(Course $course, DateTime $startDate, DateTime $endDate, ?int $riskThreshold = 70, ?int $riskConsecutiveLabCount = 2): CourseInstance
    {
        $courseInstanceRepo = $this->em->getRepository(CourseInstance::class);
        $courseInstance = (new CourseInstance())
            ->setDates(new CourseDates($startDate, $endDate))
            ->setIndexInCourse($courseInstanceRepo->getNextIndexInCourse($course))
            ->setRiskThreshold($riskThreshold)
            ->setRiskConsecutiveLabCount($riskConsecutiveLabCount)
            ->setCourse($course);

        return $this->save($courseInstance);
    }

    public function createEnrolment(Student $student, CourseInstance $courseInstance): Enrolment
    {
        $enrolment = (new Enrolment())
            ->setStudent($student)
            ->setCourseInstance($courseInstance);

        return $this->save($enrolment);
    }

    public function createLab(string $name, DateTime $startDateTime, CourseInstance $courseInstance): Lab
    {
        $lab = (new Lab())
            ->setName($name)
            ->setStartDateTime($startDateTime)
            ->setCourseInstance($courseInstance);

        return $this->save($lab);
    }

    public function createAffectiveField(string $name, string $lowLabel, string $highLabel): AffectiveField
    {
        $affectiveField = (new AffectiveField())
            ->setName($name)
            ->setLowLabel($lowLabel)
            ->setHighLabel($highLabel);

        return $this->save($affectiveField);
    }

    public function createXYQuestion(string $name, string $questionText, AffectiveField $xField, AffectiveField $yField): XYQuestion
    {
        $xyQuestion = (new XYQuestion())
            ->setName($name)
            ->setQuestionText($questionText)
            ->setXField($xField)
            ->setYField($yField);

        return $this->save($xyQuestion);
    }

    public function createLabXYQuestion(int $index, XYQuestion $xyQuestion, Lab $lab): LabXYQuestion
    {
        $labXYQuestion = (new LabXYQuestion())
            ->setIndex($index)
            ->setXYQuestion($xyQuestion)
            ->setLab($lab);

        return $this->save($labXYQuestion);
    }

    public function createSentimentQuestion(string $name, string $questionText): SentimentQuestion
    {
        $sentimentQuestion = (new SentimentQuestion())
            ->setName($name)
            ->setQuestionText($questionText);

        return $this->save($sentimentQuestion);
    }

    public function createLabSentimentQuestion(int $index, SentimentQuestion $sentimentQuestion, Lab $lab): LabSentimentQuestion
    {
        $labSentimentQuestion = (new LabSentimentQuestion())
            ->setIndex($index)
            ->setSentimentQuestion($sentimentQuestion)
            ->setLab($lab);

        return $this->save($labSentimentQuestion);
    }

    public function createLabResponse(bool $submitted, Student $student, Lab $lab): LabResponse
    {
        $labResponse = (new LabResponse())
            ->setSubmitted($submitted)
            ->setLab($lab)
            ->setStudent($student);

        return $this->save($labResponse);
    }

    public function createLabXYQuestionResponse(XYCoordinates $coordinates, LabXYQuestion $question, LabResponse $response): LabXYQuestionResponse
    {
        $xyResponse = (new LabXYQuestionResponse())
            ->setCoordinates($coordinates)
            ->setLabXYQuestion($question)
            ->setLabResponse($response);

        return  $this->save($xyResponse);
    }

    public function createLabXYQuestionDangerZone(int $riskLevel, int $xMin, int $xMax, int $yMin, int $yMax, LabXYQuestion $question): LabXYQuestionDangerZone
    {
        $labXYQuestionDangerZone = (new LabXYQuestionDangerZone)
            ->setRiskLevel($riskLevel)
            ->setXBound(new Bound($xMin, $xMax))
            ->setYBound(new Bound($yMin, $yMax))
            ->setLabXYQuestion($question);

        return $this->save($labXYQuestionDangerZone);
    }

    public function createLabSentimentQuestionResponse(string $text, string $classification, float $confidence, LabSentimentQuestion $question, LabResponse $response): LabSentimentQuestionResponse
    {
        $sentimentResponse = (new LabSentimentQuestionResponse())
            ->setText($text)
            ->setClassification($classification)
            ->setConfidence($confidence)
            ->setLabSentimentQuestion($question)
            ->setLabResponse($response);

        return  $this->save($sentimentResponse);
    }

    public function createLabSentimentQuestionDangerZone(int $riskLevel, string $classification, float $confidenceMin, float $confidenceMax, LabSentimentQuestion $question): LabSentimentQuestionDangerZone
    {
        $labSentimentQuestionDangerZone = (new LabSentimentQuestionDangerZone)
            ->setRiskLevel($riskLevel)
            ->setConfidenceBound(new Bound($confidenceMin, $confidenceMax))
            ->setClassification($classification)
            ->setLabSentimentQuestion($question);

        return $this->save($labSentimentQuestionDangerZone);
    }
}
