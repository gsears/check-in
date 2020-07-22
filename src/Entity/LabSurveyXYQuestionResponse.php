<?php

namespace App\Entity;

use App\Entity\XYCoordinates;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\LabSurveyResponse;
use App\Entity\LabSurveyXYQuestion;
use App\Repository\LabSurveyXYQuestionResponseRepository;

/**
 * @ORM\Entity(repositoryClass=LabSurveyXYQuestionResponseRepository::class)
 */
class LabSurveyXYQuestionResponse implements SurveyQuestionResponseInterface
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
     * @ORM\ManyToOne(targetEntity=LabSurveyXYQuestion::class, inversedBy="responses")
     */
    private $labSurveyXYQuestion;

    /**
     * @ORM\ManyToOne(targetEntity=LabSurveyResponse::class, inversedBy="xyQuestionResponses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $labSurveyResponse;

    public function __toString(): string
    {
        return sprintf(
            "Response for '%s': {%d,%d}\n",
            $this->getLabSurveyXYQuestion()->getXYQuestion()->getName(),
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

    public function getLabSurveyXYQuestion(): ?LabSurveyXYQuestion
    {
        return $this->labSurveyXYQuestion;
    }

    public function setLabSurveyXYQuestion(?LabSurveyXYQuestion $labSurveyXYQuestion): self
    {
        $this->labSurveyXYQuestion = $labSurveyXYQuestion;

        return $this;
    }

    public function getLabSurveyResponse(): ?LabSurveyResponse
    {
        return $this->labSurveyResponse;
    }

    public function setLabSurveyResponse(?LabSurveyResponse $labSurveyResponse): self
    {
        $this->labSurveyResponse = $labSurveyResponse;

        return $this;
    }
}
