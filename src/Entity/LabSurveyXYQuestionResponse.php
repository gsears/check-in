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
     * @ORM\ManyToOne(targetEntity=Student::class, inversedBy="labSurveyXYQuestionResponses")
     * @ORM\JoinColumn(nullable=false, referencedColumnName="guid")
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity=LabSurveyXYQuestion::class, inversedBy="responses")
     */
    private $labSurveyXYQuestion;

    public function __toString() : string
    {
        $labSurveyXYQuestion = $this->getLabSurveyXYQuestion();

        return sprintf("Response from Student %s for '%s'\n%sResponse: {%d,%d}\n\n",
            $this->getStudent()->getGuid(),
            $labSurveyXYQuestion->getXYQuestion()->getName(),
            $labSurveyXYQuestion->getLabSurvey(),
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

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;

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
}
