<?php

namespace App\Task;

use App\Entity\CourseInstance;
use App\Entity\Enrolment;
use Doctrine\ORM\EntityManagerInterface;
use Rewieer\TaskSchedulerBundle\Task\AbstractScheduledTask;
use Rewieer\TaskSchedulerBundle\Task\Schedule;

class FlagStudentsTask extends AbstractScheduledTask
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function initialize(Schedule $schedule)
    {
        $schedule
            ->everyMinutes(5); // Every week the task is run.
    }

    public function run()
    {
        // Get all course instances that are active
        $courseInstanceRepo = $this->entityManager->getRepository(CourseInstance::class);
        $courseInstances = $courseInstanceRepo->findAllActive();

        // Get all enrolment risk objects for students at risk based on course thresholds etc.
        $enrolmentRepo = $this->entityManager->getRepository(Enrolment::class);
        $enrolmentRisksPerCourse = array_map(function ($courseInstance) use ($enrolmentRepo) {
            return $enrolmentRepo->findEnrolmentRisksByCourseInstance($courseInstance, true);
        }, $courseInstances);

        // Flatten
        $enrolmentRisks = array_merge(...$enrolmentRisksPerCourse);

        // Flag the student (as automatically flagged)
        foreach ($enrolmentRisks as $enrolmentRisk) {
            $enrolmentRisk->getEnrolment()->setRiskFlag(Enrolment::FLAG_AUTOMATIC);
        }

        $this->entityManager->flush();
    }
}
