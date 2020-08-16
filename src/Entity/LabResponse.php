<?php

namespace App\Entity;

use App\Repository\LabResponseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LabResponseRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class LabResponse
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
     * @ORM\ManyToOne(targetEntity=Student::class, inversedBy="labResponses")
     * @ORM\JoinColumn(nullable=false, referencedColumnName="guid")
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity=Lab::class, inversedBy="responses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lab;

    /**
     * @ORM\OneToMany(targetEntity=LabXYQuestionResponse::class, mappedBy="labResponse", orphanRemoval=true, cascade={"persist"})
     */
    private $labXYQuestionResponses;

    /**
     * @ORM\OneToMany(targetEntity=LabSentimentQuestionResponse::class, mappedBy="labResponse", orphanRemoval=true)
     */
    private $labSentimentQuestionResponses;

    /**
     * @ORM\Column(type="boolean")
     */
    private $submitted;

    public function __construct()
    {
        $this->labXYQuestionResponses = new ArrayCollection();
        $this->labSentimentQuestionResponses = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf(
            "%s's response for %s ():\n%s\n",
            $this->getStudent()->getGuid(),
            $this->getLab()->getName(),
            join("", $this->getLabXYQuestionResponses()->toArray())
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

    public function getLab(): ?Lab
    {
        return $this->lab;
    }

    public function setLab(?Lab $lab): self
    {
        $this->lab = $lab;

        return $this;
    }

    /**
     * @return Collection|LabXYQuestionResponse[]
     */
    public function getLabXYQuestionResponses(): Collection
    {
        return $this->labXYQuestionResponses;
    }

    public function addLabXYQuestionResponse(LabXYQuestionResponse $xyQuestionResponse): self
    {
        if (!$this->labXYQuestionResponses->contains($xyQuestionResponse)) {
            $this->labXYQuestionResponses[] = $xyQuestionResponse;
            $xyQuestionResponse->setLabResponse($this);
        }

        return $this;
    }

    public function removeLabXYQuestionResponse(LabXYQuestionResponse $xyQuestionResponse): self
    {
        if ($this->labXYQuestionResponses->contains($xyQuestionResponse)) {
            $this->labXYQuestionResponses->removeElement($xyQuestionResponse);
            // set the owning side to null (unless already changed)
            if ($xyQuestionResponse->getLabResponse() === $this) {
                $xyQuestionResponse->setLabResponse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LabSentimentQuestionResponse[]
     */
    public function getLabSentimentQuestionResponses(): Collection
    {
        return $this->labSentimentQuestionResponses;
    }

    public function addLabSentimentQuestionResponse(LabSentimentQuestionResponse $labSentimentQuestionResponse): self
    {
        if (!$this->labSentimentQuestionResponses->contains($labSentimentQuestionResponse)) {
            $this->labSentimentQuestionResponses[] = $labSentimentQuestionResponse;
            $labSentimentQuestionResponse->setLabResponse($this);
        }

        return $this;
    }

    public function removeLabSentimentQuestionResponse(LabSentimentQuestionResponse $labSentimentQuestionResponse): self
    {
        if ($this->labSentimentQuestionResponses->contains($labSentimentQuestionResponse)) {
            $this->labSentimentQuestionResponses->removeElement($labSentimentQuestionResponse);
            // set the owning side to null (unless already changed)
            if ($labSentimentQuestionResponse->getLabResponse() === $this) {
                $labSentimentQuestionResponse->setLabResponse(null);
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
            $this->setCreatedAt($this->getUpdatedAt());
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

    /**
     * @return Collection|SurveyQuestionResponseInterface[]
     */
    public function getQuestionResponses(): Collection
    {
        // Merge question responses
        return new ArrayCollection(array_merge(
            $this->labXYQuestionResponses->toArray(),
            $this->labSentimentQuestionResponses->toArray()
        ));
    }
}
