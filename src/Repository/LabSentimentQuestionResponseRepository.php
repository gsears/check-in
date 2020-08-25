<?php

/*
LabSentimentQuestionResponseRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\LabSentimentQuestionResponse;
use App\Entity\SurveyQuestionResponseInterface;
use App\Containers\Risk\SurveyQuestionResponseRisk;
use App\Containers\Risk\LabSentimentQuestionResponseRisk;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    public function getSurveyQuestionResponseRisk(SurveyQuestionResponseInterface $questionResponse): SurveyQuestionResponseRisk
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT dz.riskLevel
            FROM App\Entity\LabSentimentQuestionResponse sr
            JOIN sr.labSentimentQuestion sq
            JOIN sq.dangerZones dz
            WHERE sr = :questionResponse AND
            dz.classification = sr.classification AND
            dz.confidenceMin <= sr.confidence AND
            dz.confidenceMax > sr.confidence'
        )->setParameter('questionResponse', $questionResponse);

        try {
            $labSentimentQuestionResponseRisk = new LabSentimentQuestionResponseRisk(
                $query->getSingleScalarResult(),
                $questionResponse
            );
        } catch (\Doctrine\ORM\NoResultException $e) {
            //  No result, so we know there is no risk.
            $labSentimentQuestionResponseRisk = new LabSentimentQuestionResponseRisk(
                SurveyQuestionResponseRisk::LEVEL_NONE,
                $questionResponse
            );
        }

        return $labSentimentQuestionResponseRisk;
    }
}
