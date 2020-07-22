<?php

namespace App\Entity;

use App\Repository\InstructorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InstructorRepository::class)
 */
class Instructor
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="instructor", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $appuser;

    /**
     * @ORM\ManyToMany(targetEntity=CourseInstance::class, mappedBy="instructors")
     */
    private $courseInstances;

    public function __construct()
    {
        $this->courseInstances = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "INSTRUCTOR - " . $this->getUser();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->appuser;
    }

    public function setUser(User $appuser): self
    {
        $this->appuser = $appuser;

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
            $courseInstance->addInstructor($this);
        }

        return $this;
    }

    public function removeCourseInstance(CourseInstance $courseInstance): self
    {
        if ($this->courseInstances->contains($courseInstance)) {
            $this->courseInstances->removeElement($courseInstance);
            $courseInstance->removeInstructor($this);
        }

        return $this;
    }
}
