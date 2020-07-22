<?php

namespace App\Repository;

use App\Entity\XYQuestionDangerZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method XYQuestionDangerZone|null find($id, $lockMode = null, $lockVersion = null)
 * @method XYQuestionDangerZone|null findOneBy(array $criteria, array $orderBy = null)
 * @method XYQuestionDangerZone[]    findAll()
 * @method XYQuestionDangerZone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class XYQuestionDangerZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, XYQuestionDangerZone::class);
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
