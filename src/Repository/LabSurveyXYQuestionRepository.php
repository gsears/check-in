<?php

namespace App\Repository;

use App\Entity\LabSurveyXYQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LabSurveyXYQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabSurveyXYQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabSurveyXYQuestion[]    findAll()
 * @method LabSurveyXYQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabSurveyXYQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabSurveyXYQuestion::class);
    }
}
