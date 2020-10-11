<?php

/*
LabXYQuestionResponseRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use App\Entity\LabXYQuestionResponse;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\SurveyQuestionResponseInterface;
use App\Containers\Risk\LabXYQuestionResponseRisk;
use App\Containers\Risk\SurveyQuestionResponseRisk;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Default symfony methods provided via annotations
 *
 * @method LabXYQuestionResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabXYQuestionResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabXYQuestionRespons e[]    findAll()
 * @method LabXYQuestionResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabXYQuestionResponseRepository extends ServiceEntityRepository implements SurveyQuestionResponseRepositoryInterface
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabXYQuestionResponse::class);
    }

    /**
     * Returns the SurveyQuestionResponseRisk object for this type of question.
     *
     * It essentially checks if the responses are within the danger zones associated with
     * the XYQuestion, and gives them the appropriate risk level if they are.
     *
     * @param SurveyQuestionResponseInterface $questionResponse
     * @return SurveyQuestionResponseRisk
     */
    public function getSurveyQuestionResponseRisk(SurveyQuestionResponseInterface $questionResponse): SurveyQuestionResponseRisk
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
            $labXYQuestionResponseRisk = new LabXYQuestionResponseRisk(
                $query->getSingleScalarResult(),
                $questionResponse
            );
        } catch (\Doctrine\ORM\NoResultException $e) {
            //  No result, so we know there is no risk.
            $labXYQuestionResponseRisk = new LabXYQuestionResponseRisk(
                SurveyQuestionResponseRisk::LEVEL_NONE,
                $questionResponse
            );
        }

        return $labXYQuestionResponseRisk;
    }
}
