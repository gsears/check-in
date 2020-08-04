<?php

namespace App\Repository;

use App\Entity\LabXYQuestionResponse;
use App\Containers\LabResponseRisk;
use App\Entity\SurveyQuestionResponseInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

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

    public function getRiskLevel(SurveyQuestionResponseInterface $questionResponse): int
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT dz.riskLevel
            FROM App\Entity\LabXyQuestionResponse xyr
            JOIN xyr.labXYQuestion xyq
            JOIN xyq.dangerZones dz
            WHERE xyr = :questionResponse AND
                dz.xMin <= xyr.xValue AND
                dz.xMax >= xyr.xValue AND
                dz.yMin <= xyr.yValue AND
                dz.yMax >= xyr.yValue'
        )->setParameter('questionResponse', $questionResponse);

        try {
            $riskLevel = $query->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            //  No result, so we know there is no risk.
            return LabResponseRisk::LEVEL_NONE;
        }

        if (!LabResponseRisk::isValidRiskLevel($riskLevel)) {
            throw new InvalidTypeException("Invalid risk level fetched: " . $riskLevel, 1);
        }

        return $riskLevel;
    }
}
