<?php

namespace App\Entity;

use App\Entity\XYCoordinates;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\LabResponse;
use App\Entity\LabXYQuestion;
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
     */
    private $labXYQuestion;

    /**
     * @ORM\ManyToOne(targetEntity=LabResponse::class, inversedBy="xyQuestionResponses")
     * @ORM\JoinColumn(nullable=false)
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

    public function getCoordinates(): ?XYCoordinates
    {
        if ($this->xValue && $this->yValue) {
            return new XYCoordinates($this->xValue, $this->yValue);
        } else {
            return null;
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
