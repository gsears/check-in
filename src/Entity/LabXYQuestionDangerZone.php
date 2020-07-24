<?php

namespace App\Entity;

use App\Repository\LabXYQuestionDangerZoneRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LabXYQuestionDangerZoneRepository::class)
 */
class LabXYQuestionDangerZone
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Cascade persist means we don't need to explicitly call persist on XYDangerZones when we update
     * a lab question with new ones.
     *
     * @ORM\ManyToOne(targetEntity=LabXYQuestion::class, inversedBy="dangerZones")
     * @ORM\JoinColumn(nullable=false)
     */
    private $labXYQuestion;

    /**
     * @ORM\Column(type="integer")
     */
    private $riskLevel;

    /**
     * @ORM\Column(type="integer")
     */
    private $yMax;

    /**
     * @ORM\Column(type="integer")
     */
    private $yMin;

    /**
     * @ORM\Column(type="integer")
     */
    private $xMax;

    /**
     * @ORM\Column(type="integer")
     */
    private $xMin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabXYQuestion(): ?LabXYQuestion
    {
        return $this->labXYQuestion;
    }

    public function setLabXYQuestion(?LabXYQuestion $labXYQuestion): self
    {
        $this->labXYQuestion = $labXYQuestion;

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

    public function getYMax(): ?int
    {
        return $this->yMax;
    }

    public function setYMax(int $yMax): self
    {
        $this->yMax = $yMax;

        return $this;
    }

    public function getYMin(): ?int
    {
        return $this->yMin;
    }

    public function setYMin(int $yMin): self
    {
        $this->yMin = $yMin;

        return $this;
    }

    public function getXMax(): ?int
    {
        return $this->xMax;
    }

    public function setXMax(int $xMax): self
    {
        $this->xMax = $xMax;

        return $this;
    }

    public function getXMin(): ?int
    {
        return $this->xMin;
    }

    public function setXMin(int $xMin): self
    {
        $this->xMin = $xMin;

        return $this;
    }
}
