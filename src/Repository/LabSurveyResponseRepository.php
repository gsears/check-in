<?php

namespace App\Repository;

use App\Entity\LabSurveyResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LabSurveyResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabSurveyResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabSurveyResponse[]    findAll()
 * @method LabSurveyResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabSurveyResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabSurveyResponse::class);
    }

    /**
     * @return LabSurveyResponse[] Returns an array of LabSurveyResponse objects
     */

    public function findByLabSurveyAndStudent($labSurvey, $student)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.student = :student')
            ->setParameter('student', $student)
            ->andWhere('l.labSurvey = :labSurvey')
            ->setParameter('student', $labSurvey)
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?LabSurveyResponse
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
