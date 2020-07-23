<?php

namespace App\Entity;

use App\Repository\XYQuestionDangerZoneRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=XYQuestionDangerZoneRepository::class)
 */
class XYQuestionDangerZone
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=LabXYQuestion::class, inversedBy="dangerZones")
     * @ORM\JoinColumn(nullable=false)
     */
    private $labXYQuestion;

    /**
     * @ORM\Column(type="integer")
     */
    private $riskWeight;

    /**
     * @ORM\Column(type="integer")
     */
    private $yHighBound;

    /**
     * @ORM\Column(type="integer")
     */
    private $yLowBound;

    /**
     * @ORM\Column(type="integer")
     */
    private $xHighBound;

    /**
     * @ORM\Column(type="integer")
     */
    private $xLowBound;

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

    public function getRiskWeight(): ?int
    {
        return $this->riskWeight;
    }

    public function setRiskWeight(int $riskWeight): self
    {
        $this->riskWeight = $riskWeight;

        return $this;
    }

    public function getYHighBound(): ?int
    {
        return $this->yHighBound;
    }

    public function setYHighBound(int $yHighBound): self
    {
        $this->yHighBound = $yHighBound;

        return $this;
    }

    public function getYLowBound(): ?int
    {
        return $this->yLowBound;
    }

    public function setYLowBound(int $yLowBound): self
    {
        $this->yLowBound = $yLowBound;

        return $this;
    }

    public function getXHighBound(): ?int
    {
        return $this->xHighBound;
    }

    public function setXHighBound(int $xHighBound): self
    {
        $this->xHighBound = $xHighBound;

        return $this;
    }

    public function getXLowBound(): ?int
    {
        return $this->xLowBound;
    }

    public function setXLowBound(int $xLowBound): self
    {
        $this->xLowBound = $xLowBound;

        return $this;
    }
}
