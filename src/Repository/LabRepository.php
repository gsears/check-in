<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\CourseInstance;
use App\Entity\Instructor;
use App\Entity\Lab;
use App\Entity\LabResponse;
use App\Entity\Student;
use App\Provider\DateTimeProvider;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
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
     * @return void
     */
    public function findStudentsAtRiskByLab(Lab $lab)
    {
        $responseRepo = $this->getEntityManager()->getRepository(LabResponse::class);

        $riskCollection = $lab->getResponses()
            ->filter(function (LabResponse $response) {
                return $response->getSubmitted();
            })
            ->map(function ($response) use ($responseRepo) {
                return $responseRepo->getRiskForResponse($response);
            });

        $riskIterator = $riskCollection->getIterator();

        // Order by highest risk
        $riskIterator->uasort(function ($a, $b) {
            return ($a->getRiskFactor() > $b->getRiskFactor()) ? -1 : 1;
        });

        return iterator_to_array($riskIterator);
    }
}
