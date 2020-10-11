<?php

/*
XYQuestionRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use App\Entity\XYQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Default symfony methods provided via annotations.
 * 
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
}
