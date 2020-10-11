<?php

/*
AffectiveFieldRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use App\Entity\AffectiveField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Default symfony methods provided via annotations.
 *
 * @method AffectiveField|null find($id, $lockMode = null, $lockVersion = null)
 * @method AffectiveField|null findOneBy(array $criteria, array $orderBy = null)
 * @method AffectiveField[]    findAll()
 * @method AffectiveField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AffectiveFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffectiveField::class);
    }
}
