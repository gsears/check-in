<?php

/*
LabResponseRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use App\Entity\LabResponse;
use App\Containers\Risk\LabResponseRisk;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method LabResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method LabResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method LabResponse[]    findAll()
 * @method LabResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabResponse::class);
    }

    public function findByCourseInstanceAndStudent($courseInstance, $student): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.lab', 'l')
            ->andWhere('l.courseInstance = :courseInstance')
            ->setParameter('courseInstance', $courseInstance)
            ->andWhere('r.student = :student')
            ->setParameter('student', $student)
            ->orderBy('l.startDateTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets the lab response for a given student and lab.
     *
     * @return LabResponse
     */
    public function findOneByLabAndStudent($lab, $student): ?LabResponse
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.student = :student')
            ->setParameter('student', $student)
            ->andWhere('l.lab = :lab')
            ->setParameter('lab', $lab)
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Gets all completed surveys for a student in a given course instance.
     *
     * @return LabResponse[]
     */
    public function findCompletedByCourseInstanceAndStudent($courseInstance, $student): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.lab', 'l')
            ->andWhere('l.courseInstance = :courseInstance')
            ->setParameter('courseInstance', $courseInstance)
            ->andWhere('r.student = :student')
            ->setParameter('student', $student)
            ->andWhere('r.submitted = true')
            ->orderBy('l.startDateTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns a LabResponseRisk container object for a given lab response. This contains
     * all the SurveyQuestionResponse risks and other helper functions for working with
     * student risks.
     *
     * @param LabResponse The lab response for which to calculate risks.
     * @return LabResponseRisk
     */
    public function getLabResponseRisk(LabResponse $labResponse)
    {
        $surveyQuestionResponseRisks = $labResponse->getQuestionResponses()->map(
            function ($question) {
                /**
                 * Dynamically get the correct respository for the question response. The shared
                 * interface guarantees a method which returns the response risk object for that
                 * question.
                 * @var SurveyQuestionResponseRepository
                 */
                $surveyQuestionRepo = $this->getEntityManager()->getRepository(get_class($question));
                return $surveyQuestionRepo->getSurveyQuestionResponseRisk($question);
            }
        )->toArray();

        return new LabResponseRisk($surveyQuestionResponseRisks, $labResponse);
    }
}
