<?php

/*
SentimentQuestionRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use App\Entity\SentimentQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Default symfony methods provided via annotations.
 * 
 * @method SentimentQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method SentimentQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method SentimentQuestion[]    findAll()
 * @method SentimentQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SentimentQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SentimentQuestion::class);
    }
}
