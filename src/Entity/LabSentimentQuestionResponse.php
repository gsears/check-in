<?php

namespace App\Entity;

use App\Repository\LabSentimentQuestionResponseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LabSentimentQuestionResponseRepository::class)
 */
class LabSentimentQuestionResponse implements SurveyQuestionResponseInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity=LabSentimentQuestion::class, inversedBy="responses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $labSentimentQuestion;

    /**
     * @ORM\ManyToOne(targetEntity=LabResponse::class, inversedBy="labSentimentQuestionResponses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $labResponse;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $classification;

    /**
     * @ORM\Column(type="float")
     */
    private $confidence;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getLabSentimentQuestion(): ?LabSentimentQuestion
    {
        return $this->labSentimentQuestion;
    }

    public function setLabSentimentQuestion(?LabSentimentQuestion $labSentimentQuestion): self
    {
        $this->labSentimentQuestion = $labSentimentQuestion;

        return $this;
    }

    public function getLabResponse(): ?LabResponse
    {
        return $this->labResponse;
    }

    public function setLabResponse(?LabResponse $labResponse): self
    {
        $this->labResponse = $labResponse;

        return $this;
    }

    public function getClassification(): ?string
    {
        return $this->classification;
    }

    public function setClassification(string $classification): self
    {
        $this->classification = $classification;

        return $this;
    }

    public function getConfidence(): ?float
    {
        return $this->confidence;
    }

    public function setConfidence(float $confidence): self
    {
        $this->confidence = $confidence;

        return $this;
    }
}
