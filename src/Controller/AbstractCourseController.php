<?php

/*
AbstractCourseController.php
Gareth Sears - 2493194S
*/

namespace App\Controller;

use App\Entity\Lab;
use App\Entity\Student;
use App\Entity\CourseInstance;
use App\Repository\LabRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CourseInstanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractCourseController extends AbstractController
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function fetchCourseInstance($index, $course)
    {
        /**
         * @var CourseInstanceRepository
         */
        $courseInstanceRepo = $this->entityManager->getRepository(CourseInstance::class);
        $courseInstance = $courseInstanceRepo->findByIndexAndCourseCode($index, $course);

        if (!$courseInstance) throw $this->createNotFoundException('This course does not exist');
        return $courseInstance;
    }

    protected function fetchStudent($studentId)
    {
        /**
         * @var StudentRepository
         */
        $studentRepo =  $this->entityManager->getRepository(Student::class);
        $student = $studentRepo->find($studentId);

        if (!$student) throw $this->createNotFoundException('This student does not exist');
        return $student;
    }

    protected function fetchLab($labSlug, $courseInstance)
    {
        /**
         * @var LabRepository
         */
        $labRepo = $this->entityManager->getRepository(Lab::class);

        $lab = $labRepo->findOneBy([
            "slug" => $labSlug,
            "courseInstance" => $courseInstance,
        ]);

        if (!$lab) throw $this->createNotFoundException('This lab does not exist in this course');
        return $lab;
    }
}
