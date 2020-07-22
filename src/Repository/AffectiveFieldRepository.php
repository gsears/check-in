<?php

namespace App\Repository;

use App\Entity\AffectiveField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AffectiveField|null find($id, $lockMode = null, $lockVersion = null)
 * @method AffectiveField|null findOneBy(array $criteria, array $orderBy = null)
 * @method AffectiveField[]    findAll()
 * @method AffectiveField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AffectiveFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffectiveField::class);
    }

    // /**
    //  * @return AffectiveField[] Returns an array of AffectiveField objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AffectiveField
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
