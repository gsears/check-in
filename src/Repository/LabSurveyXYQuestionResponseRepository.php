<?php

namespace App\Repository;

use App\Entity\LabSurveyXYQuestionResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LabSurveyXYQuestionResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabSurveyXYQuestionResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabSurveyXYQuestionResponse[]    findAll()
 * @method LabSurveyXYQuestionResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabSurveyXYQuestionResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabSurveyXYQuestionResponse::class);
    }

    // /**
    //  * @return LabSurveyXYQuestionResponse[] Returns an array of LabSurveyXYQuestionResponse objects
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
    public function findOneBySomeField($value): ?LabSurveyXYQuestionResponse
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
