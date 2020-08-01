<?php

namespace App\Repository;

use App\Entity\LabXYQuestionResponse;
use App\Entity\LabResponseRisk;
use App\Entity\SurveyQuestionResponseInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LabXYQuestionResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabXYQuestionResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabXYQuestionResponse[]    findAll()
 * @method LabXYQuestionResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabXYQuestionResponseRepository extends ServiceEntityRepository implements SurveyQuestionResponseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabXYQuestionResponse::class);
    }

    public function getRiskLevel(SurveyQuestionResponseInterface $xyQuestionResponse): int
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT dz.riskLevel
            FROM App\Entity\LabXyQuestionResponse xyr
            JOIN xyr.labXYQuestion xyq
            JOIN xyq.dangerZones dz
            WHERE xyr = :xyResponse AND
                dz.xMin <= xyr.xValue AND
                dz.xMax >= xyr.xValue AND
                dz.yMin <= xyr.yValue AND
                dz.yMax >= xyr.yValue'
        )->setParameter('xyResponse', $xyQuestionResponse);

        try {
            return $query->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            //  No result, so we know there is no risk.
            return LabResponseRisk::NONE;
        }
    }
}
