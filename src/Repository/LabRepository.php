<?php

namespace App\Repository;

use App\Entity\CourseInstance;
use App\Entity\Lab;
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
    public function findOneBySomeField($value): ?Lab
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