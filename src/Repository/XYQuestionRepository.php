<?php

namespace App\Repository;

use App\Entity\XYQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method XYQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method XYQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method XYQuestion[]    findAll()
 * @method XYQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class XYQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, XYQuestion::class);
    }




    /*
    public function findOneBySomeField($value): ?XYQuestion
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
