<?php

namespace App\Entity;

use App\Repository\LabSurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LabSurveyRepository::class)
 */
class LabSurvey
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDateTime;

    /**
     * @ORM\ManyToOne(targetEntity=CourseInstance::class, inversedBy="labSurveys")
     * @ORM\JoinColumn(nullable=false)
     */
    private $courseInstance;

    /**
     * @ORM\OneToMany(targetEntity=LabSurveyXYQuestion::class, mappedBy="labSurvey", orphanRemoval=true)
     */
    private $xyQuestions;

    /**
     * @ORM\OneToMany(targetEntity=LabSurveyResponse::class, mappedBy="labSurvey", orphanRemoval=true)
     */
    private $responses;

    public function __construct()
    {
        $this->xyQuestions = new ArrayCollection();
        $this->responses = new ArrayCollection();
    }

    public function __toString(): string
    {
        $courseInstance  = $this->getCourseInstance();
        $course = $courseInstance->getCourse();

        return sprintf(
            "Lab Survey - %s - %s\nCourse - %s\nQuestions:\n%s\n\n",
            $this->getName(),
            date_format($this->getStartDateTime(), "l d/m/y H:i"),
            $course->getCode() . ' ' . $courseInstance->getId(),
            join("\n", $this->getQuestions()->toArray())
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $labName): self
    {
        $this->name = $labName;

        return $this;
    }

    public function getStartDateTime(): ?\DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTimeInterface $startDateTime): self
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getCourseInstance(): ?CourseInstance
    {
        return $this->courseInstance;
    }

    public function setCourseInstance(?CourseInstance $courseInstance): self
    {
        $this->courseInstance = $courseInstance;

        return $this;
    }

    /**
     * @return Collection|LabSurveyXYQuestion[]
     */
    public function getXyQuestions(): Collection
    {
        return $this->xyQuestions;
    }

    public function addXyQuestion(LabSurveyXYQuestion $xyQuestion): self
    {
        if (!$this->xyQuestions->contains($xyQuestion)) {
            $this->xyQuestions[] = $xyQuestion;
            $xyQuestion->setLabSurvey($this);
        }

        return $this;
    }

    public function removeXyQuestion(LabSurveyXYQuestion $xyQuestion): self
    {
        if ($this->xyQuestions->contains($xyQuestion)) {
            $this->xyQuestions->removeElement($xyQuestion);
            // set the owning side to null (unless already changed)
            if ($xyQuestion->getLabSurvey() === $this) {
                $xyQuestion->setLabSurvey(null);
            }
        }

        return $this;
    }

    /**
     * Returns all the question in this survey of all types in order
     * @return Collection|SurveyQuestionInterface[]
     */
    public function getQuestions(): Collection
    {
        // Join here.
        $collection = $this->getXyQuestions();

        // Order here.
        $iterator = $collection->getIterator();
        $iterator->uasort(function ($a, $b) {
            return ($a->getIndex() < $b->getIndex()) ? -1 : 1;
        });

        return new ArrayCollection(iterator_to_array($iterator));
    }

    public function getQuestionCount(): int
    {
        return count($this->getQuestions()->toArray());
    }

    /**
     * @return Collection|LabSurveyResponse[]
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(LabSurveyResponse $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses[] = $response;
            $response->setLabSurvey($this);
        }

        return $this;
    }

    public function removeResponse(LabSurveyResponse $response): self
    {
        if ($this->responses->contains($response)) {
            $this->responses->removeElement($response);
            // set the owning side to null (unless already changed)
            if ($response->getLabSurvey() === $this) {
                $response->setLabSurvey(null);
            }
        }

        return $this;
    }
}
