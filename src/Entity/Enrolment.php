<?php

namespace App\Entity;

use App\Repository\EnrolmentRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EnrolmentRepository::class)
 */
class Enrolment
{
    const FLAG_AUTOMATIC = 1;
    const FLAG_BY_INSTRUCTOR = 2;
    const FLAG_BY_STUDENT = 3;


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

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $riskFlag;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $riskFlagDateTime;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $riskReason;

    public function __toString(): string
    {
        return sprintf(
            "Enrolment: Student %s <-> Course %s\n",
            $this->getStudent()->getUser()->getFullname(),
            $this->getCourseInstance()->getCourse()->getCode()
        );
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

    public function getRiskFlag(): ?int
    {
        return $this->riskFlag;
    }

    public function setRiskFlag(int $riskFlag, ?string $riskReason = null): self
    {
        $this->riskFlag = $riskFlag;
        $this->riskFlagDateTime = new DateTime();
        $this->riskReason = $riskReason;

        return $this;
    }

    public function removeRiskFlag(): self
    {
        $this->riskFlag = null;
        $this->riskFlagDateTime = null;
        $this->riskReason = null;

        return $this;
    }

    public function getRiskFlagDateTime(): ?\DateTime
    {
        return $this->riskFlagDateTime;
    }

    public function getRiskReason(): ?string
    {
        return $this->riskReason;
    }
}
