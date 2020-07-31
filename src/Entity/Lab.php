<?php

namespace App\Entity;

use App\Repository\LabRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
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
     * @ORM\OneToMany(targetEntity=LabResponse::class, mappedBy="lab", orphanRemoval=true)
     */
    private $responses;

    public function __construct()
    {
        $this->labXYQuestions = new ArrayCollection();
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
     * Returns all the question in this survey of all types in order
     * @return Collection|SurveyQuestionInterface[]
     */
    public function getQuestions(): Collection
    {
        // Join here.
        $collection = $this->getLabXYQuestions();

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
