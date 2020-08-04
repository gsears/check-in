<?php

namespace App\Repository;

use App\Entity\LabSentimentQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LabSentimentQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabSentimentQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabSentimentQuestion[]    findAll()
 * @method LabSentimentQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabSentimentQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabSentimentQuestion::class);
    }

    // /**
    //  * @return LabSentimentQuestion[] Returns an array of LabSentimentQuestion objects
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
    public function findOneBySomeField($value): ?LabSentimentQuestion
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
