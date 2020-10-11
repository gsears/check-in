<?php

/*
LabSentimentQuestionDangerZoneRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use App\Entity\LabSentimentQuestionDangerZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Default symfony methods provided via annotations.
 * 
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
}
