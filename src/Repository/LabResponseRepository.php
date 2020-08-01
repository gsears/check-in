<?php

namespace App\Repository;

use App\Entity\LabResponse;
use App\Entity\LabResponseRisk;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    /**
     * @return LabResponse Returns a single lab response object.
     */

    public function findOneByLabAndStudent($lab, $student)
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

    public function findCompletedByCourseInstanceAndStudent($courseInstance, $student)
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

    public function getRiskForResponse(LabResponse $labResponse)
    {
        $riskLevels = $labResponse->getQuestionResponses()->map(
            function ($question) {
                return $question->getRiskLevel();
            }
        )->toArray();

        dump($riskLevels);

        return new LabResponseRisk($riskLevels, $labResponse->getStudent());
    }
}
