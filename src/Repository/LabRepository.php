<?php

/*
LabRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use DateTime;
use App\Entity\Lab;
use App\Entity\Course;
use App\Entity\Student;
use App\Entity\Instructor;
use App\Entity\LabResponse;
use App\Entity\CourseInstance;
use App\Provider\DateTimeProvider;
use App\Containers\Risk\LabResponseRisk;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Default symfony methods provided via annotations.
 * 
 * @method Lab|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lab|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lab[]    findAll()
 * @method Lab[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lab::class);
    }

    /**
     * Finds all labs for a course instance before a given date. Defaults to the current date.
     * 
     * @return Lab[] Returns an array of Lab objects
     */

    public function findByCourseInstanceBeforeDate(CourseInstance $courseInstance, DateTime $beforeDateTime = null)
    {
        $beforeDateTime = $beforeDateTime ? $beforeDateTime : (new DateTimeProvider())->getCurrentDateTime();

        return $this->createQueryBuilder('l')
            ->andWhere('l.courseInstance = :courseInstance')
            ->setParameter('courseInstance', $courseInstance)
            ->andWhere('l.startDateTime < :beforeDateTime')
            ->setParameter('beforeDateTime', $beforeDateTime)
            ->orderBy('l.startDateTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds labs by course and the course instance index. Can optionally query if the results are before
     * a particular datetime.
     *
     * @param Course $course
     * @param integer $instanceIndex
     * @param DateTime $beforeDateTime
     */
    public function findByCourseAndInstanceIndex(Course $course, int $instanceIndex, DateTime $beforeDateTime = null)
    {
        $beforeDateTime = $beforeDateTime ? $beforeDateTime : (new DateTimeProvider())->getCurrentDateTime();

        return $this->createQueryBuilder('l')
            ->join('l.courseInstance', 'ci')
            ->andWhere('ci.course = :course')
            ->setParameter('course', $course)
            ->andWhere('ci.indexInCourse = :instanceIndex')
            ->setParameter('instanceIndex', $instanceIndex)
            ->andWhere('l.startDateTime < :beforeDateTime')
            ->setParameter('beforeDateTime', $beforeDateTime)
            ->orderBy('l.startDateTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the most recently started labs for an instructor.
     *
     * @param Instructor $instructor
     * @param integer $maxResults
     */
    public function findLatestByInstructor(Instructor $instructor, int $maxResults)
    {
        $beforeDateTime = (new DateTimeProvider())->getCurrentDateTime();

        return $this->createQueryBuilder('l')
            ->join('l.courseInstance', 'ci')
            ->join('ci.instructors', 'i')
            ->andWhere('i = :instructor')
            ->setParameter('instructor', $instructor)
            ->andWhere('l.startDateTime < :beforeDateTime')
            ->setParameter('beforeDateTime', $beforeDateTime)
            ->orderBy('l.startDateTime', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the most recent labs for a student which they have yet to respond to.
     *
     * @param Student $student
     * @param integer $maxResults
     */
    public function findLatestPendingByStudent(Student $student, int $maxResults)
    {
        $beforeDateTime = (new DateTimeProvider())->getCurrentDateTime();

        return $this->createQueryBuilder('l')
            ->join('l.responses', 'r')
            ->andWhere('r.student = :student')
            ->setParameter('student', $student)
            ->andWhere('r.submitted = false')
            ->andWhere('l.startDateTime < :beforeDateTime')
            ->setParameter('beforeDateTime', $beforeDateTime)
            ->orderBy('l.startDateTime', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the labs which a student has responded to for a particular student in a particular course.
     *
     * @param CourseInstance $courseInstance
     * @param Student $student
     */
    public function findCompletedSurveysByCourseInstanceAndStudent(CourseInstance $courseInstance, Student $student)
    {
        return $this->createQueryBuilder('l')
            ->join('l.responses', 'r')
            ->andWhere('l.courseInstance = :courseInstance')
            ->setParameter('courseInstance', $courseInstance)
            ->andWhere('r.student = :student')
            ->setParameter('student', $student)
            ->andWhere('r.submitted = true')
            ->orderBy('l.startDateTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
      * Returns the labs which a student has yet to respond to for a particular student in a particular course.
     *
     * @param CourseInstance $courseInstance
     * @param Student $student
     * @param DateTime $beforeDateTime
     */
    public function findPendingSurveysByCourseInstanceAndStudent(CourseInstance $courseInstance, Student $student, DateTime $beforeDateTime = null)
    {
        $beforeDateTime = $beforeDateTime ? $beforeDateTime : (new DateTimeProvider())->getCurrentDateTime();

        return $this->createQueryBuilder('l')
            ->join('l.responses', 'r')
            ->andWhere('l.courseInstance = :courseInstance')
            ->setParameter('courseInstance', $courseInstance)
            ->andWhere('r.student = :student')
            ->setParameter('student', $student)
            ->andWhere('r.submitted = false')
            ->andWhere('l.startDateTime < :beforeDateTime')
            ->setParameter('beforeDateTime', $beforeDateTime)
            ->orderBy('l.startDateTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns risk for students who have completed lab surveys.
     *
     * @param Lab $lab
     * @return LabResponseRisk[]
     */
    public function getLabResponseRisks(Lab $lab)
    {
        /**
         * @var LabResponseRepository
         */
        $responseRepo = $this->getEntityManager()->getRepository(LabResponse::class);

        // Get the labResponseRisks for each submitted survey response
        // array_values resets numbering after filter for sorting
        $labResponseRisks = array_values($lab->getResponses()
            ->filter(function (LabResponse $response) {
                return $response->getSubmitted();
            })
            ->map(function ($response) use ($responseRepo) {
                return $responseRepo->getLabResponseRisk($response);
            })
            ->toArray());

        return $labResponseRisks;
    }
}
