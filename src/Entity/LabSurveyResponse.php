<?php

namespace App\Entity;

use App\Repository\LabSurveyResponseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LabSurveyResponseRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class LabSurveyResponse
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Student::class, inversedBy="labSurveyResponses")
     * @ORM\JoinColumn(nullable=false, referencedColumnName="guid")
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity=LabSurvey::class, inversedBy="responses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $labSurvey;

    /**
     * @ORM\OneToMany(targetEntity=LabSurveyXYQuestionResponse::class, mappedBy="labSurveyResponse", orphanRemoval=true)
     */
    private $xyQuestionResponses;

    /**
     * @ORM\Column(type="boolean")
     */
    private $submitted;

    public function __construct()
    {
        $this->xyQuestionResponses = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf(
            "%s's response for %s (%s):\n%s\n",
            $this->getStudent()->getGuid(),
            $this->getLabSurvey()->getName(),
            $this->getCreatedAt()->format("d/m/y h:i:s"),
            join("", $this->getXyQuestionResponses()->toArray())
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
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

    public function getLabSurvey(): ?LabSurvey
    {
        return $this->labSurvey;
    }

    public function setLabSurvey(?LabSurvey $labSurvey): self
    {
        $this->labSurvey = $labSurvey;

        return $this;
    }

    /**
     * @return Collection|LabSurveyXYQuestionResponse[]
     */
    public function getXyQuestionResponses(): Collection
    {
        return $this->xyQuestionResponses;
    }

    public function addXyQuestionResponse(LabSurveyXYQuestionResponse $xyQuestionResponse): self
    {
        if (!$this->xyQuestionResponses->contains($xyQuestionResponse)) {
            $this->xyQuestionResponses[] = $xyQuestionResponse;
            $xyQuestionResponse->setLabSurveyResponse($this);
        }

        return $this;
    }

    public function removeXyQuestionResponse(LabSurveyXYQuestionResponse $xyQuestionResponse): self
    {
        if ($this->xyQuestionResponses->contains($xyQuestionResponse)) {
            $this->xyQuestionResponses->removeElement($xyQuestionResponse);
            // set the owning side to null (unless already changed)
            if ($xyQuestionResponse->getLabSurveyResponse() === $this) {
                $xyQuestionResponse->setLabSurveyResponse(null);
            }
        }

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTime('now'));
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }

    public function getSubmitted(): ?bool
    {
        return $this->submitted;
    }

    public function setSubmitted(bool $submitted): self
    {
        $this->submitted = $submitted;

        return $this;
    }
}
