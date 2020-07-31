<?php

namespace App\Repository;

use App\Entity\LabXYQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LabXYQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabXYQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabXYQuestion[]    findAll()
 * @method LabXYQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabXYQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabXYQuestion::class);
    }
}
