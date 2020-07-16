<?php

namespace App\Entity;

use App\Repository\EnrolmentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EnrolmentRepository::class)
 */
class Enrolment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Student::class, inversedBy="enrolments")
     * @ORM\JoinColumn(nullable=false, referencedColumnName="guid")
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity=CourseInstance::class, inversedBy="enrolments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $courseInstance;

    public function __toString() : string
    {
        return sprintf("Enrolment: Student %s <-> Course %s\n",
            $this->getStudent()->getUser()->getFullname(),
            $this->getCourseInstance()->getCourse()->getCode());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getCourseInstance(): ?CourseInstance
    {
        return $this->courseInstance;
    }

    public function setCourseInstance(?CourseInstance $courseInstance): self
    {
        $this->courseInstance = $courseInstance;

        return $this;
    }
}
