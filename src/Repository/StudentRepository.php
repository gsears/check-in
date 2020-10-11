<?php

/*
StudentRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Default symfony methods provided via annotations.
 * 
 * @method Student|null find($id, $lockMode = null, $lockVersion = null)
 * @method Student|null findOneBy(array $criteria, array $orderBy = null)
 * @method Student[]    findAll()
 * @method Student[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Student::class);
    }

    /**
     * Returns all students in a particular course instance
     *
     * @param [type] $courseInstance
     */
    public function findByCourseInstance($courseInstance)
    {
        return $this->createQueryBuilder('s')
            ->join('s.enrolments', 'e')
            ->andWhere('e.courseInstance = :courseInstance')
            ->setParameter('courseInstance', $courseInstance)
            ->getQuery()
            ->getResult();
    }
}
