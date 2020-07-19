<?php

namespace App\Entity;

use App\Repository\LabXYQuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LabSurveyXYQuestionRepository::class)
 */
class LabSurveyXYQuestion // implements SurveyQuestionInterface
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

    /**
     * @ORM\OneToMany(targetEntity=LabSurveyXYQuestionResponse::class, mappedBy="labSurveyXYQuestion")
     */
    private $responses;

    /**
     * The order of the question in the survey
     *
     * @ORM\Column(type="integer")
     */
    private $index;

    public function __construct()
    {
        $this->responses = new ArrayCollection();
    }

    public function __toString() : string
    {
        return sprintf('Q%d : %s',
            $this->getIndex(),
            $this->getXyQuestion()->getName());
    }

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

    /**
     * @return Collection|LabSurveyXYQuestionResponse[]
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(LabSurveyXYQuestionResponse $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses[] = $response;
            $response->setLabSurveyXYQuestion($this);
        }

        return $this;
    }

    public function removeResponse(LabSurveyXYQuestionResponse $response): self
    {
        if ($this->responses->contains($response)) {
            $this->responses->removeElement($response);
            // set the owning side to null (unless already changed)
            if ($response->getLabSurveyXYQuestion() === $this) {
                $response->setLabSurveyXYQuestion(null);
            }
        }

        return $this;
    }

    public function getIndex(): ?int
    {
        return $this->index;
    }

    public function setIndex(int $index): self
    {
        $this->index = $index;

        return $this;
    }
}
