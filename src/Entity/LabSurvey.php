<?php

namespace App\Entity;

use App\Repository\LabSurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LabSurveyRepository::class)
 */
class LabSurvey
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $labName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDateTime;

    /**
     * @ORM\ManyToOne(targetEntity=CourseInstance::class, inversedBy="labSurveys")
     * @ORM\JoinColumn(nullable=false)
     */
    private $courseInstance;

    /**
     * @ORM\OneToMany(targetEntity=LabSurveyXYQuestion::class, mappedBy="labSurvey", orphanRemoval=true)
     */
    private $xyQuestions;

    public function __construct()
    {
        $this->xyQuestions = new ArrayCollection();
    }

    public function __toString() : string
    {
        $courseInstance  = $this->getCourseInstance();
        $course = $courseInstance->getCourse();

        return sprintf("Lab Survey - %s - %s\nCourse - %s\n",
            $this->getLabName(),
            date_format($this->getStartDateTime(), "l d/m/y H:i"),
            $course->getCode() . ' ' . $courseInstance->getId());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabName(): ?string
    {
        return $this->labName;
    }

    public function setLabName(string $labName): self
    {
        $this->labName = $labName;

        return $this;
    }

    public function getStartDateTime(): ?\DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTimeInterface $startDateTime): self
    {
        $this->startDateTime = $startDateTime;

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

    /**
     * @return Collection|LabSurveyXYQuestion[]
     */
    public function getXyQuestions(): Collection
    {
        return $this->xyQuestions;
    }

    public function addXyQuestion(LabSurveyXYQuestion $xyQuestion): self
    {
        if (!$this->xyQuestions->contains($xyQuestion)) {
            $this->xyQuestions[] = $xyQuestion;
            $xyQuestion->setLabSurvey($this);
        }

        return $this;
    }

    public function removeXyQuestion(LabSurveyXYQuestion $xyQuestion): self
    {
        if ($this->xyQuestions->contains($xyQuestion)) {
            $this->xyQuestions->removeElement($xyQuestion);
            // set the owning side to null (unless already changed)
            if ($xyQuestion->getLabSurvey() === $this) {
                $xyQuestion->setLabSurvey(null);
            }
        }

        return $this;
    }
}
