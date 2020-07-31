<?php

namespace App\Repository;

use App\Entity\LabResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LabResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabResponse[]    findAll()
 * @method LabResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabResponse::class);
    }

    /**
     * @return LabResponse Returns a single lab response object.
     */

    public function findOneByLabAndStudent($lab, $student)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.student = :student')
            ->setParameter('student', $student)
            ->andWhere('l.lab = :lab')
            ->setParameter('lab', $lab)
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
