<?php

namespace App\Repository;

use App\Containers\LabResponseRisk;
use App\Entity\LabSentimentQuestionResponse;
use App\Entity\SurveyQuestionResponseInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

/**
 * @method LabSentimentQuestionResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabSentimentQuestionResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabSentimentQuestionResponse[]    findAll()
 * @method LabSentimentQuestionResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabSentimentQuestionResponseRepository extends ServiceEntityRepository implements SurveyQuestionResponseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabSentimentQuestionResponse::class);
    }

    public function getRiskLevel(SurveyQuestionResponseInterface $questionResponse): int
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT dz.riskLevel
            FROM App\Entity\LabSentimentQuestionResponse sr
            JOIN sr.labSentimentQuestion sq
            JOIN sq.dangerZones dz
            WHERE sr = :questionResponse AND
            dz.classification = sq.classification AND
            dz.confidenceMin <= sq.confidence AND
            dz.confidenceMax > sq.confidence'
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
