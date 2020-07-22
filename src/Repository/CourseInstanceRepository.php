<?php

namespace App\Repository;

use App\Entity\CourseInstance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CourseInstance|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseInstance|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseInstance[]    findAll()
 * @method CourseInstance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseInstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseInstance::class);
    }

    /**
     * Finds all CourseInstance objects which match the student.
     * @return CourseInstance[] Returns an array of CourseInstance objects
     */

    public function findByStudent($student)
    {
        return $this->createQueryBuilder('c')
            ->join('c.enrolments', 'e')
            ->andWhere('e.student = :student')
            ->setParameter('student', $student)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all CourseInstance objects which match the instructor.
     * @return CourseInstance[] Returns an array of CourseInstance objects
     */

    public function findByInstructor($instructor)
    {
        return $this->createQueryBuilder('c')
            ->join('c.instructors', 'i')
            ->andWhere('i = :instructor')
            ->setParameter('instructor', $instructor)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }


    /*
    public function findOneBySomeField($value): ?CourseInstance
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
