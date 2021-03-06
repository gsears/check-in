<?php

/*
InstructorRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use App\Entity\Instructor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Default symfony methods provided via annotations.
 * 
 * @method Instructor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Instructor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Instructor[]    findAll()
 * @method Instructor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstructorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Instructor::class);
    }

    /**
     * Find all instructors for a particular student.
     * 
     * @return Instructor[] Returns an array of Instructor objects
     */

    public function findByStudent($student)
    {
        return $this->createQueryBuilder('i')
            ->join('i.courseInstances', 'c')
            ->join('c.enrolments', 'e')
            ->andWhere('e.student = :student')
            ->setParameter('student', $student)
            ->orderBy('i.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
