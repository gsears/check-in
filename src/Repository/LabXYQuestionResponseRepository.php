<?php

namespace App\Repository;

use App\Entity\LabXYQuestionResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LabXYQuestionResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabXYQuestionResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabXYQuestionResponse[]    findAll()
 * @method LabXYQuestionResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabXYQuestionResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabXYQuestionResponse::class);
    }
}
