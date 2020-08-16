<?php

/*
EnrolmentRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;

use App\Entity\Enrolment;
use App\Entity\CourseInstance;
use App\Containers\EnrolmentRisk;
use App\Entity\LabResponse;
use App\Provider\DateTimeProvider;
use DateTime;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enrolment::class);
    }

    /**
     * This is the main function for getting students at risk for a particular course instance.
     *
     * @param CourseInstance $courseInstance
     * @param boolean $onlyAtRisk
     * @param DateTime $currentTime
     * @return EnrolmentRisk[] A collection of enrolment risk wrapper objects which contain risk information
     * as well as student and enrolment information.
     */
    public function findEnrolmentRisksByCourseInstance(CourseInstance $courseInstance, bool $onlyAtRisk = false, DateTime $currentTime = null): array
    {
        // If no time is provided, set the current date time.
        if (!$currentTime) {
            $currentTime = (new DateTimeProvider)->getCurrentDateTime();
        }

        // Get the risk settings for the course instance
        $riskThreshold = $courseInstance->getRiskThreshold();
        $consecutiveLabCount = $courseInstance->getRiskConsecutiveLabCount();

        // Get previous X labs from date, according to consecutive lab count.
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

        // Get the student responses for each of the labs returned above
        $query = $this->getEntityManager()->createQuery('
            SELECT lr FROM App\Entity\LabResponse lr
            WHERE lr.lab IN (:labs)
            ORDER BY lr.student ASC
        ');
        $query->setParameter('labs', $labs);
        $labResponses = $query->getResult();

        // Split the response so each student's responses are chunked together
        $responsesByStudent = array_chunk($labResponses, $consecutiveLabCount);

        /**
         * @var LabResponseRepository
         */
        $labResponseRepo = $this->getEntityManager()->getRepository(LabResponse::class);

        // Get the enrolment risks.
        // For every student's response set...
        $enrolmentRisks = array_map(function ($responseChunk) use ($courseInstance, $labResponseRepo) {
            // Get the labResponseRisk object
            $labResponseRisks = array_map(function ($response) use ($labResponseRepo) {
                return $labResponseRepo->getLabResponseRisk($response);
            }, $responseChunk);

            // Get the student and the enrolment for that student...
            $student = $responseChunk[0]->getStudent();
            $enrolment = $this->findOneBy([
                'student' => $student,
                'courseInstance' => $courseInstance
            ]);

            // And wrap them in a container for further processing.
            return new EnrolmentRisk($labResponseRisks, $enrolment);
        }, $responsesByStudent);

        // If the only at risk parameter is passed, filter out students who are not at risk according
        // to the risk threshold configuration.
        if ($onlyAtRisk) {
            $enrolmentRisks = array_filter(
                $enrolmentRisks,
                function (EnrolmentRisk $enrolmentRisk) use ($riskThreshold) {
                    return $enrolmentRisk->areAllRisksAbove($riskThreshold);
                }
            );
        }

        // Sort the results by average risk.
        EnrolmentRisk::sortByAverageRisk($enrolmentRisks);

        return $enrolmentRisks;
    }
}
