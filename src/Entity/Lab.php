<?php

namespace App\Entity;

use App\Repository\LabRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * TODO: Error checks for valid question indicies, although this is
 * not needed until users can create their own surveys.
 *
 * @ORM\Entity(repositoryClass=LabRepository::class)
 * @UniqueEntity(
 *     fields={"courseInstance", "name"},
 *     errorPath="name",
 *     message="Lab names must be unique for each course instance."
 * )
 */
class Lab
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
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDateTime;

    /**
     * @ORM\ManyToOne(targetEntity=CourseInstance::class, inversedBy="labs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $courseInstance;

    /**
     * @ORM\OneToMany(targetEntity=LabXYQuestion::class, mappedBy="lab", orphanRemoval=true)
     */
    private $labXYQuestions;

    /**
     * @ORM\OneToMany(targetEntity=LabSentimentQuestion::class, mappedBy="lab", orphanRemoval=true)
     */
    private $labSentimentQuestions;

    /**
     * @ORM\OneToMany(targetEntity=LabResponse::class, mappedBy="lab", orphanRemoval=true)
     */
    private $responses;

    private $questionCount;

    public function __construct()
    {
        $this->labXYQuestions = new ArrayCollection();
        $this->responses = new ArrayCollection();
        $this->labSentimentQuestions = new ArrayCollection();
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
        $this->slug = strtolower((new AsciiSlugger())->slug($labName));

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
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
     * @return Collection|LabXYQuestion[]
     */
    public function getLabXYQuestions(): Collection
    {
        return $this->labXYQuestions;
    }

    public function addLabXYQuestion(LabXYQuestion $xyQuestion): self
    {
        if (!$this->labXYQuestions->contains($xyQuestion)) {
            $this->labXYQuestions[] = $xyQuestion;
            $xyQuestion->setLab($this);
        }

        return $this;
    }

    public function removeLabXYQuestion(LabXYQuestion $xyQuestion): self
    {
        if ($this->labXYQuestions->contains($xyQuestion)) {
            $this->labXYQuestions->removeElement($xyQuestion);
            // set the owning side to null (unless already changed)
            if ($xyQuestion->getLab() === $this) {
                $xyQuestion->setLab(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LabSentimentQuestion[]
     */
    public function getLabSentimentQuestions(): Collection
    {
        return $this->labSentimentQuestions;
    }

    public function addLabSentimentQuestion(LabSentimentQuestion $labSentimentQuestion): self
    {
        if (!$this->labSentimentQuestions->contains($labSentimentQuestion)) {
            $this->labSentimentQuestions[] = $labSentimentQuestion;
            $labSentimentQuestion->setLab($this);
        }

        return $this;
    }

    public function removeLabSentimentQuestion(LabSentimentQuestion $labSentimentQuestion): self
    {
        if ($this->labSentimentQuestions->contains($labSentimentQuestion)) {
            $this->labSentimentQuestions->removeElement($labSentimentQuestion);
            // set the owning side to null (unless already changed)
            if ($labSentimentQuestion->getLab() === $this) {
                $labSentimentQuestion->setLab(null);
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
        // Merge all questions
        $questions = array_merge(
            $this->labXYQuestions->toArray(),
            $this->labSentimentQuestions->toArray()
        );

        // Sort by question order
        uasort($questions, function ($a, $b) {
            return ($a->getIndex() < $b->getIndex()) ? -1 : 1;
        });

        // Return as a collection
        return new ArrayCollection($questions);
    }

    public function getQuestionCount(): int
    {
        return count($this->getQuestions()->toArray());
    }

    /**
     * @return Collection|LabResponse[]
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(LabResponse $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses[] = $response;
            $response->setLab($this);
        }

        return $this;
    }

    public function removeResponse(LabResponse $response): self
    {
        if ($this->responses->contains($response)) {
            $this->responses->removeElement($response);
            // set the owning side to null (unless already changed)
            if ($response->getLab() === $this) {
                $response->setLab(null);
            }
        }

        return $this;
    }
}
