<?php

/*
LabSentimentQuestionRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use App\Entity\LabSentimentQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Default symfony methods provided via annotations.
 * 
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
}
