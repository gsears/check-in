<?php

namespace App\Repository;

use App\Entity\Enrolment;
use App\Entity\CourseInstance;
use App\Containers\EnrolmentRisk;
use App\Provider\DateTimeProvider;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Enrolment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Enrolment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Enrolment[]    findAll()
 * @method Enrolment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnrolmentRepository extends ServiceEntityRepository
{

    private $labResponseRepo;

    public function __construct(ManagerRegistry $registry, LabResponseRepository $labResponseRepo)
    {
        parent::__construct($registry, Enrolment::class);
        $this->labResponseRepo = $labResponseRepo;
    }

    public function findEnrolmentRisksByCourseInstance(CourseInstance $courseInstance, bool $onlyAtRisk = false)
    {
        $currentTime = (new DateTimeProvider)->getCurrentDateTime();
        $riskThreshold = $courseInstance->getRiskThreshold();
        $consecutiveLabCount = $courseInstance->getRiskConsecutiveLabCount();

        // Get previous X labs from date
        $query = $this->getEntityManager()->createQuery('
            SELECT l FROM App\Entity\Lab l
            JOIN l.courseInstance ci
            WHERE ci = :courseInstance AND l.startDateTime <= :currentTime
            ORDER BY l.startDateTime DESC
        ');

        $query->setParameter('courseInstance', $courseInstance);
        $query->setParameter('currentTime', $currentTime);
        $query->setMaxResults($consecutiveLabCount);
        $labs = $query->getResult();

        // If there aren't enough labs to make a calculation, return nothing.
        if (count($labs) < $consecutiveLabCount) {
            return [];
        }

        $query = $this->getEntityManager()->createQuery('
            SELECT lr FROM App\Entity\LabResponse lr
            WHERE lr.lab IN (:labs)
            ORDER BY lr.student ASC
        ');
        $query->setParameter('labs', $labs);
        $labResponses = $query->getResult();

        $responsesByStudent = array_chunk($labResponses, $consecutiveLabCount);

        $enrolmentRisks = array_map(function ($responseChunk) use ($courseInstance) {
            $labResponseRisks = array_map(function ($response) {
                return $this->labResponseRepo->getLabResponseRisk($response);
            }, $responseChunk);

            $student = $responseChunk[0]->getStudent();
            $enrolment = $this->findOneBy([
                'student' => $student,
                'courseInstance' => $courseInstance
            ]);

            return new EnrolmentRisk($labResponseRisks, $enrolment);
        }, $responsesByStudent);

        if ($onlyAtRisk) {
            $enrolmentRisks = array_filter($enrolmentRisks, function (EnrolmentRisk $enrolmentRisk) use ($riskThreshold) {
                return $enrolmentRisk->areAllRisksAbove($riskThreshold);
            });
        }

        EnrolmentRisk::sortByAverageRisk($enrolmentRisks);

        return $enrolmentRisks;
    }
}
