<?php

/*
LabXYQuestionDangerZoneRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use App\Entity\LabXYQuestionDangerZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Default symfony methods provided via annotations.
 * 
 * @method LabXYQuestionDangerZone|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabXYQuestionDangerZone|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabXYQuestionDangerZone[]    findAll()
 * @method LabXYQuestionDangerZone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabXYQuestionDangerZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabXYQuestionDangerZone::class);
    }
}
