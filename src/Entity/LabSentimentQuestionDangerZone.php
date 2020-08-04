<?php

namespace App\Entity;

use App\Containers\Bound;
use App\Repository\LabSentimentQuestionDangerZoneRepository;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * @ORM\Entity(repositoryClass=LabSentimentQuestionDangerZoneRepository::class)
 */
class LabSentimentQuestionDangerZone implements SurveyQuestionDangerZoneInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=LabSentimentQuestion::class, inversedBy="dangerZones")
     * @ORM\JoinColumn(nullable=false)
     */
    private $labSentimentQuestion;

    /**
     * @ORM\Column(type="integer")
     */
    private $riskLevel;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $classification;

    /**
     * @ORM\Column(type="float")
     */
    private $confidenceMin;

    /**
     * @ORM\Column(type="float")
     */
    private $confidenceMax;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRiskLevel(): ?int
    {
        return $this->riskLevel;
    }

    public function setRiskLevel(int $riskLevel): self
    {
        $this->riskLevel = $riskLevel;

        return $this;
    }

    public function getClassification(): ?string
    {
        return $this->classification;
    }

    public function setClassification(string $classification): self
    {
        if (!SentimentQuestion::isValidClassification($classification)) {
            throw new InvalidArgumentException(sprintf($classification . " is not a valid classification."), 1);
        }

        $this->classification = $classification;

        return $this;
    }

    public function getConfidenceMin(): ?float
    {
        return $this->confidenceMin;
    }

    public function getConfidenceMax(): ?float
    {
        return $this->confidenceMax;
    }

    public function setConfidenceBound(Bound $bound): self
    {
        $this->confidenceMin = $bound->getLowBound();
        $this->confidenceMax = $bound->getHighBound();

        return $this;
    }
}
