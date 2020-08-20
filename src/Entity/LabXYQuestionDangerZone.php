<?php

namespace App\Entity;

use App\Containers\Bound;
use App\Containers\Risk\SurveyQuestionResponseRisk;
use App\Repository\LabXYQuestionDangerZoneRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LabXYQuestionDangerZoneRepository::class)
 */
class LabXYQuestionDangerZone implements SurveyQuestionDangerZoneInterface
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
        if (!SurveyQuestionResponseRisk::isValidRiskLevel($riskLevel)) {
            throw new \InvalidArgumentException($riskLevel . " is not a valid risk level. See SurveyQuestionResponseRisk constants.", 1);
        }

        $this->riskLevel = $riskLevel;

        return $this;
    }

    public function setYBound(Bound $bound): self
    {
        $this->yMin = $bound->getLowBound();
        $this->yMax = $bound->getHighBound();

        return $this;
    }

    public function getYMax(): ?int
    {
        return $this->yMax;
    }

    public function getYMin(): ?int
    {
        return $this->yMin;
    }

    public function setXBound(Bound $bound): self
    {
        $this->xMin = $bound->getLowBound();
        $this->xMax = $bound->getHighBound();

        return $this;
    }

    public function getXMax(): ?int
    {
        return $this->xMax;
    }

    public function getXMin(): ?int
    {
        return $this->xMin;
    }
}
