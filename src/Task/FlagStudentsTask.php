<?php

namespace App\Task;

use App\Entity\CourseInstance;
use App\Entity\Enrolment;
use Doctrine\ORM\EntityManagerInterface;
use Rewieer\TaskSchedulerBundle\Task\AbstractScheduledTask;
use Rewieer\TaskSchedulerBundle\Task\Schedule;

class FlagStudentsTask extends AbstractScheduledTask
{
    const CRON_EXPRESSION = "*/5 * * * *";
    const CRON_DESCRIPTION = "every 5 minutes";

    private $entityManager;

    /**
     * Inject entity manager as a service
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function initialize(Schedule $schedule)
    {
        $schedule->getCron()->setExpression(self::CRON_EXPRESSION);
    }

    public function run()
    {
        /**
         * Get all course instances that are active
         * @var CourseInstanceRepository
         */
        $courseInstanceRepo = $this->entityManager->getRepository(CourseInstance::class);
        $courseInstances = $courseInstanceRepo->findAllActive();

        /**
         * Get all enrolment risk objects for students at risk based on course thresholds etc.
         * @var EnrolmentRepository
         */
        $enrolmentRepo = $this->entityManager->getRepository(Enrolment::class);
        $enrolmentRisksPerCourse = array_map(function ($courseInstance) use ($enrolmentRepo) {
            return $enrolmentRepo->findEnrolmentRisksByCourseInstance($courseInstance, true);
        }, $courseInstances);

        // Flatten
        $enrolmentRisks = array_merge(...$enrolmentRisksPerCourse);

        // Flag the student (as automatically flagged)
        foreach ($enrolmentRisks as $enrolmentRisk) {
            $enrolment = $enrolmentRisk->getEnrolment();

            // If they are not already flagged
            if (is_null($enrolment->getRiskFlag())) {
                $courseInstance = $enrolment->getCourseInstance();
                $reason = sprintf(
                    "Your lab risk factor has been over %d%% for %d consecutive labs. This was the latest threshold set by course instructors prior to when automatic detection was scheduled, which is currently %s.",
                    $courseInstance->getRiskThreshold(),
                    $courseInstance->getRiskConsecutiveLabCount(),
                    self::CRON_DESCRIPTION
                );
                $enrolment->setRiskFlag(Enrolment::FLAG_AUTOMATIC, $reason);
            }
        }

        $this->entityManager->flush();
    }
}
