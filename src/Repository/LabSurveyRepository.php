<?php

namespace App\Repository;

use App\Entity\LabSurvey;
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

    // /**
    //  * @return LabSurvey[] Returns an array of LabSurvey objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

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
