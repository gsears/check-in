<?php

namespace App\Repository;

use App\Entity\CourseInstance;
use App\Entity\LabSurvey;
use App\Entity\Student;
use App\Provider\DateTimeProvider;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LabSurvey|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabSurvey|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabSurvey[]    findAll()
 * @method LabSurvey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabSurveyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabSurvey::class);
    }

    /**
     * @return LabSurvey[] Returns an array of LabSurvey objects
     */

    public function findByCourseInstance(CourseInstance $courseInstance, DateTime $beforeDateTime = null)
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

    /*
    public function findOneBySomeField($value): ?LabSurvey
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
