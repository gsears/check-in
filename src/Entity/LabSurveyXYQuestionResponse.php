<?php

namespace App\Entity;

use App\Repository\LabSurveyXYQuestionResponseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LabSurveyXYQuestionResponseRepository::class)
 */
class LabSurveyXYQuestionResponse
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

    public function __toString() : string
    {
        return sprintf("Response for '%s': {%d,%d}\n",
            $this->getLabSurveyXYQuestion()->getXYQuestion()->getName(),
            $this->getXValue(),
            $this->getYValue()
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

    public function setXValue(?int $xValue): self
    {
        $this->xValue = $xValue;

        return $this;
    }

    public function getYValue(): ?int
    {
        return $this->yValue;
    }

    public function setYValue(?int $yValue): self
    {
        $this->yValue = $yValue;

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
