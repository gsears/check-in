<?php

namespace App\Entity;

use App\Repository\LabSentimentQuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LabSentimentQuestionRepository::class)
 */
class LabSentimentQuestion implements SurveyQuestionInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Lab::class, inversedBy="labSentimentQuestions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lab;

    /**
     * @ORM\ManyToOne(targetEntity=SentimentQuestion::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $sentimentQuestion;

    /**
     * Change the name to prevent functional test syntax errors.
     * @ORM\Column(type="integer", name="questionIndex"))
     * @Assert\NotNull
     */
    private $index;

    /**
     * @ORM\OneToMany(targetEntity=LabSentimentQuestionDangerZone::class, mappedBy="labSentimentQuestion", orphanRemoval=true)
     */
    private $dangerZones;

    /**
     * @ORM\OneToMany(targetEntity=LabSentimentQuestionResponse::class, mappedBy="labSentimentQuestion", orphanRemoval=true)
     */
    private $responses;

    public function __construct()
    {
        $this->dangerZones = new ArrayCollection();
        $this->responses = new ArrayCollection();
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

    public function getSentimentQuestion(): ?SentimentQuestion
    {
        return $this->sentimentQuestion;
    }

    public function setSentimentQuestion(?SentimentQuestion $sentimentQuestion): self
    {
        $this->sentimentQuestion = $sentimentQuestion;

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
        return $this->sentimentQuestion;
    }

    /**
     * @return Collection|LabSentimentQuestionDangerZone[]
     */
    public function getDangerZones(): Collection
    {
        return $this->dangerZones;
    }

    public function addDangerZone(LabSentimentQuestionDangerZone $dangerZone): self
    {
        if (!$this->dangerZones->contains($dangerZone)) {
            $this->dangerZones[] = $dangerZone;
            $dangerZone->setLabSentimentQuestion($this);
        }

        return $this;
    }

    public function removeDangerZone(LabSentimentQuestionDangerZone $dangerZone): self
    {
        if ($this->dangerZones->contains($dangerZone)) {
            $this->dangerZones->removeElement($dangerZone);
            // set the owning side to null (unless already changed)
            if ($dangerZone->getLabSentimentQuestion() === $this) {
                $dangerZone->setLabSentimentQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LabSentimentQuestionResponse[]
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(LabSentimentQuestionResponse $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses[] = $response;
            $response->setLabSentimentQuestion($this);
        }

        return $this;
    }

    public function removeResponse(LabSentimentQuestionResponse $response): self
    {
        if ($this->responses->contains($response)) {
            $this->responses->removeElement($response);
            // set the owning side to null (unless already changed)
            if ($response->getLabSentimentQuestion() === $this) {
                $response->setLabSentimentQuestion(null);
            }
        }

        return $this;
    }
}
