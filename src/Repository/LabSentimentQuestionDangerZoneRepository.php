<?php

namespace App\Repository;

use App\Entity\LabSentimentQuestionDangerZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LabSentimentQuestionDangerZone|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabSentimentQuestionDangerZone|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabSentimentQuestionDangerZone[]    findAll()
 * @method LabSentimentQuestionDangerZone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabSentimentQuestionDangerZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabSentimentQuestionDangerZone::class);
    }

    // /**
    //  * @return LabSentimentQuestionDangerZone[] Returns an array of LabSentimentQuestionDangerZone objects
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
    public function findOneBySomeField($value): ?LabSentimentQuestionDangerZone
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
