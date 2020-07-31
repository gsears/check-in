<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Lab;
use App\Entity\User;
use App\Entity\Course;
use App\Entity\Student;
use App\Entity\Enrolment;
use App\Entity\Instructor;
use App\Entity\XYQuestion;
use App\Entity\LabResponse;
use App\Entity\LabXYQuestion;
use App\Entity\XYCoordinates;
use App\Entity\AffectiveField;
use App\Entity\Bound;
use App\Entity\CourseDates;
use App\Entity\CourseInstance;
use App\Entity\LabXYQuestionResponse;
use App\Entity\LabXYQuestionDangerZone;
use App\Repository\UserRepository;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

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

    public function createUser(string $forename, string $surname, string $email): User
    {
        $user = (new User())
            ->setForename($forename)
            ->setSurname($surname)
            ->setPassword(self::PASSWORD)
            ->setEmail($email);

        return $this->save($user);
    }

    public function createStudent(string $forename, string $surname, string $guid, string $email = null): Student
    {
        if (!$email) {
            $firstSurnameLetter = strtolower($surname[0]);
            $email = $guid . $firstSurnameLetter . '@student.gla.ac.uk';
        }

        $user = $this->createUser(
            $forename,
            $surname,
            $email
        );

        $student = (new Student())
            ->setGuid($guid);

        $user->setStudent($student);

        $this->em->flush();
        return $this->save($student);
    }

    public function createInstructor(string $forename, string $surname, string $email = null): Instructor
    {
        if (!$email) {
            $email = strtolower($forename . '.' . $surname . '@glasgow.ac.uk');
        }

        $user = $this->createUser(
            $forename,
            $surname,
            $email
        );

        $instructor = new Instructor();

        $user->setInstructor($instructor);

        $this->em->flush();
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

    public function createCourseInstance(Course $course, DateTime $startDate, DateTime $endDate): CourseInstance
    {
        $courseInstanceRepo = $this->em->getRepository(CourseInstance::class);
        $courseInstance = (new CourseInstance())
            ->setDates(new CourseDates($startDate, $endDate))
            ->setIndexInCourse($courseInstanceRepo->getNextIndexInCourse($course));

        $course->addCourseInstance($courseInstance);

        return $this->save($courseInstance);
    }

    public function createEnrolment(Student $student, CourseInstance $courseInstance): Enrolment
    {
        $enrolment = (new Enrolment());
        $student->addEnrolment($enrolment);
        $courseInstance->addEnrolment($enrolment);

        return $this->save($enrolment);
    }

    public function createLab(string $name, DateTime $startDateTime, CourseInstance $courseInstance): Lab
    {
        $lab = (new Lab())
            ->setName($name)
            ->setStartDateTime($startDateTime);

        $courseInstance->addLab($lab);

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
            ->setXYQuestion($xyQuestion);

        $lab->addLabXYQuestion($labXYQuestion);

        return $this->save($labXYQuestion);
    }

    public function createLabResponse(bool $submitted, Student $student, Lab $lab): LabResponse
    {
        $labResponse = (new LabResponse())
            ->setSubmitted($submitted);

        $student->addLabResponse($labResponse);
        $lab->addResponse($labResponse);
        $this->save($labResponse);
        $this->em->flush();
        return $labResponse;
    }

    public function createLabXYQuestionResponse(XYCoordinates $coordinates, LabXYQuestion $question, LabResponse $response): LabXYQuestionResponse
    {
        $xyResponse = (new LabXYQuestionResponse())
            ->setCoordinates($coordinates);

        $question->addResponse($xyResponse);
        $response->addXYQuestionResponse($xyResponse);

        return $this->save($xyResponse);
    }

    public function createLabXYQuestionDangerZone(int $riskLevel, int $xMin, int $xMax, int $yMin, int $yMax, LabXYQuestion $question): LabXYQuestionDangerZone
    {
        $labXYQuestionDangerZone = (new LabXYQuestionDangerZone)
            ->setRiskLevel($riskLevel)
            ->setXBound(new Bound($xMin, $xMax))
            ->setYBound(new Bound($yMin, $yMax));

        $question->addDangerZone($labXYQuestionDangerZone);

        return $this->save($labXYQuestionDangerZone);
    }
}
