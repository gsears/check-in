<?php

/*
LabXYQuestionResponse.php
Gareth Sears - 2493194S
*/

namespace App\Entity;

use App\Entity\LabResponse;
use App\Entity\LabXYQuestion;
use Doctrine\ORM\Mapping as ORM;
use App\Containers\XYCoordinates;
use App\Repository\LabXYQuestionResponseRepository;

/**
 * @ORM\Entity(repositoryClass=LabXYQuestionResponseRepository::class)
 */
class LabXYQuestionResponse implements SurveyQuestionResponseInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $xValue;

    /**
     * @ORM\Column(type="integer")
     */
    private $yValue;

    /**
     * @ORM\ManyToOne(targetEntity=LabXYQuestion::class, inversedBy="responses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $labXYQuestion;

    /**
     * @ORM\ManyToOne(targetEntity=LabResponse::class, inversedBy="xyQuestionResponses")
     */
    private $labResponse;

    public function __toString(): string
    {
        return sprintf(
            "Response for '%s': {%d,%d}\n",
            $this->getLabXYQuestion()->getXYQuestion()->getName(),
            $this->xValue,
            $this->yValue
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getXValue(): ?int
    {
        return $this->xValue;
    }

    public function getYValue(): ?int
    {
        return $this->yValue;
    }

    public function getCoordinates(): ?XYCoordinates
    {
        if (is_null($this->xValue) || is_null($this->yValue)) {
            return null;
        } else {
            return new XYCoordinates($this->xValue, $this->yValue);
        }
    }

    public function setCoordinates(XYCoordinates $xyCoordinates): self
    {
        $this->xValue = $xyCoordinates->getX();
        $this->yValue = $xyCoordinates->getY();

        return $this;
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

    public function getLabResponse(): ?LabResponse
    {
        return $this->labResponse;
    }

    public function setLabResponse(?LabResponse $labResponse): self
    {
        $this->labResponse = $labResponse;

        return $this;
    }
}
