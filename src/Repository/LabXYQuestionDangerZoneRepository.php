<?php

namespace App\Repository;

use App\Entity\LabXYQuestionDangerZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LabXYQuestionDangerZone|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabXYQuestionDangerZone|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabXYQuestionDangerZone[]    findAll()
 * @method LabXYQuestionDangerZone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabXYQuestionDangerZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabXYQuestionDangerZone::class);
    }

    // /**
    //  * @return XYQuestionDangerZone[] Returns an array of XYQuestionDangerZone objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('x')
            ->andWhere('x.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('x.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?XYQuestionDangerZone
    {
        return $this->createQueryBuilder('x')
            ->andWhere('x.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
