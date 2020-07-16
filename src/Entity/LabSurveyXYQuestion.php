<?php

namespace App\Entity;

use App\Repository\LabXYQuestionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LabSurveyXYQuestionRepository::class)
 */
class LabSurveyXYQuestion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=LabSurvey::class, inversedBy="xyQuestions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $labSurvey;

    /**
     * @ORM\ManyToOne(targetEntity=XYQuestion::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $xyQuestion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabSurvey(): ?LabSurvey
    {
        return $this->labSurvey;
    }

    public function setLabSurvey(?LabSurvey $labSurvey): self
    {
        $this->labSurvey = $labSurvey;

        return $this;
    }

    public function getXyQuestion(): ?XYQuestion
    {
        return $this->xyQuestion;
    }

    public function setXyQuestion(?XYQuestion $xyQuestion): self
    {
        $this->xyQuestion = $xyQuestion;

        return $this;
    }
}
