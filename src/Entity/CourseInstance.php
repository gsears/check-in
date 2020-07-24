<?php

namespace App\Entity;

use App\Entity\CourseDates;
use App\Repository\CourseInstanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CourseInstanceRepository::class)
 */
class CourseInstance
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class, inversedBy="courseInstances")
     * @ORM\JoinColumn(nullable=false, referencedColumnName="code")
     */
    private $course;

    /**
     * @ORM\Column(type="date")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date")
     */
    private $endDate;

    /**
     * @ORM\ManyToMany(targetEntity=Instructor::class, inversedBy="courseInstances")
     */
    private $instructors;

    /**
     * @ORM\OneToMany(targetEntity=Enrolment::class, mappedBy="courseInstance", orphanRemoval=true)
     */
    private $enrolments;

    /**
     * @ORM\OneToMany(targetEntity=Lab::class, mappedBy="courseInstance", orphanRemoval=true)
     */
    private $labs;

    public function __construct()
    {
        $this->instructors = new ArrayCollection();
        $this->enrolments = new ArrayCollection();
        $this->labs = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf(
            "Course Instance: %sStartDate: %s - EndDate: %s\n \nInstructors Assigned:\n- %s\Students Assigned:\n- %s\n",
            $this->getCourse(),
            date_format($this->getStartDate(), "d/m/y"),
            date_format($this->getEndDate(), "d/m/y"),
            join("- ", $this->getInstructors()->toArray()),
            join("- ", $this->getEnrolments()->map(function ($enrolment) {
                return $enrolment->getStudent();
            })->toArray())
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function setDates(CourseDates $courseDates): self
    {
        $this->startDate = $courseDates->getStartDate();
        $this->endDate = $courseDates->getEndDate();

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @return Collection|Instructor[]
     */
    public function getInstructors(): Collection
    {
        return $this->instructors;
    }

    public function addInstructor(Instructor $instructor): self
    {
        if (!$this->instructors->contains($instructor)) {
            $this->instructors[] = $instructor;
        }

        return $this;
    }

    public function removeInstructor(Instructor $instructor): self
    {
        if ($this->instructors->contains($instructor)) {
            $this->instructors->removeElement($instructor);
        }

        return $this;
    }

    /**
     * @return Collection|Enrolment[]
     */
    public function getEnrolments(): Collection
    {
        return $this->enrolments;
    }

    public function addEnrolment(Enrolment $enrolment): self
    {
        if (!$this->enrolments->contains($enrolment)) {
            $this->enrolments[] = $enrolment;
            $enrolment->setCourseInstance($this);
        }

        return $this;
    }

    public function removeEnrolment(Enrolment $enrolment): self
    {
        if ($this->enrolments->contains($enrolment)) {
            $this->enrolments->removeElement($enrolment);
            // set the owning side to null (unless already changed)
            if ($enrolment->getCourseInstance() === $this) {
                $enrolment->setCourseInstance(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Lab[]
     */
    public function getLabs(): Collection
    {
        return $this->labs;
    }

    public function addLab(Lab $lab): self
    {
        if (!$this->labs->contains($lab)) {
            $this->labs[] = $lab;
            $lab->setCourseInstance($this);
        }

        return $this;
    }

    public function removeLab(Lab $lab): self
    {
        if ($this->labs->contains($lab)) {
            $this->labs->removeElement($lab);
            // set the owning side to null (unless already changed)
            if ($lab->getCourseInstance() === $this) {
                $lab->setCourseInstance(null);
            }
        }

        return $this;
    }

    public function getName()
    {
        return sprintf(
            "%s %s: %s - %s",
            $this->course->getCode(),
            $this->course->getName(),
            date_format($this->getStartDate(), "d/m/y"),
            date_format($this->getEndDate(), "d/m/y")
        );
    }
}
