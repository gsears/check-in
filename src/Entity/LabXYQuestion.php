<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=App\Repository\LabXYQuestionRepository::class)
 */
class LabXYQuestion implements SurveyQuestionInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Lab::class, inversedBy="labXYQuestions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lab;

    /**
     * @ORM\ManyToOne(targetEntity=XYQuestion::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $xyQuestion;

    /**
     * @ORM\OneToMany(targetEntity=LabXYQuestionResponse::class, mappedBy="labXYQuestion")
     */
    private $responses;

    /**
     * The order of the question in the survey
     *
     * @ORM\Column(type="integer")
     * @Assert\NotNull
     */
    private $index;

    /**
     * @ORM\OneToMany(targetEntity=LabXYQuestionDangerZone::class, mappedBy="labXYQuestion", orphanRemoval=true, cascade={"persist"})
     */
    private $dangerZones;

    public function __construct()
    {
        $this->responses = new ArrayCollection();
        $this->dangerZones = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf(
            'Q%d : %s',
            $this->getIndex(),
            $this->getXYQuestion()->getName()
        );
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getXYQuestion(): ?XYQuestion
    {
        return $this->xyQuestion;
    }

    public function setXYQuestion(?XYQuestion $xyQuestion): self
    {
        $this->xyQuestion = $xyQuestion;

        return $this;
    }

    /**
     * @return Collection|LabXYQuestionResponse[]
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(LabXYQuestionResponse $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses[] = $response;
            $response->setLabXYQuestion($this);
        }

        return $this;
    }

    public function removeResponse(LabXYQuestionResponse $response): self
    {
        if ($this->responses->contains($response)) {
            $this->responses->removeElement($response);
            // set the owning side to null (unless already changed)
            if ($response->getLabXYQuestion() === $this) {
                $response->setLabXYQuestion(null);
            }
        }

        return $this;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function setIndex(int $index): self
    {
        if ($index < 0) {
            throw new \InvalidArgumentException("Index cannot be < 0", 1);
        }
        $this->index = $index;

        return $this;
    }

    public function getQuestion(): QuestionInterface
    {
        return $this->xyQuestion;
    }

    /**
     * @return Collection|LabXYQuestionDangerZone[]
     */
    public function getDangerZones(): Collection
    {
        return $this->dangerZones;
    }

    public function addDangerZone(LabXYQuestionDangerZone $dangerZone): self
    {
        if (!$this->dangerZones->contains($dangerZone)) {
            $this->dangerZones[] = $dangerZone;
            $dangerZone->setLabXYQuestion($this);
        }

        return $this;
    }

    public function removeDangerZone(LabXYQuestionDangerZone $dangerZone): self
    {
        if ($this->dangerZones->contains($dangerZone)) {
            $this->dangerZones->removeElement($dangerZone);
            // set the owning side to null (unless already changed)
            if ($dangerZone->getLabXYQuestion() === $this) {
                $dangerZone->setLabXYQuestion(null);
            }
        }

        return $this;
    }
}
