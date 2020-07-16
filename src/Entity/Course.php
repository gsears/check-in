<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CourseRepository::class)
 */
class Course
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=12)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=65535, nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=CourseInstance::class, mappedBy="course", orphanRemoval=true)
     */
    private $courseInstances;

    public function __construct()
    {
        $this->courseInstances = new ArrayCollection();
    }

    public function __toString() : string
    {
        return sprintf("Code: %s - Name: %s\n",
            $this->getCode(),
            $this->getName());
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|CourseInstance[]
     */
    public function getCourseInstances(): Collection
    {
        return $this->courseInstances;
    }

    public function addCourseInstance(CourseInstance $courseInstance): self
    {
        if (!$this->courseInstances->contains($courseInstance)) {
            $this->courseInstances[] = $courseInstance;
            $courseInstance->setCourse($this);
        }

        return $this;
    }

    public function removeCourseInstance(CourseInstance $courseInstance): self
    {
        if ($this->courseInstances->contains($courseInstance)) {
            $this->courseInstances->removeElement($courseInstance);
            // set the owning side to null (unless already changed)
            if ($courseInstance->getCourse() === $this) {
                $courseInstance->setCourse(null);
            }
        }

        return $this;
    }

    public function __(Type $var = null)
    {
        # code...
    }
}
