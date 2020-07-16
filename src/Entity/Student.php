<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StudentRepository::class)
 */
class Student
{
    /**
     * Note: This is Glasgow University ID, not Globally Unique!
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $guid;

    /**
     * Relates the student to a user. Uses $appuser as USER is a postgres keyword.
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="student", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $appuser;

    /**
     * @ORM\OneToMany(targetEntity=Enrolment::class, mappedBy="student", orphanRemoval=true)
     */
    private $enrolments;

    /**
     * @ORM\OneToMany(targetEntity=LabSurveyXYQuestionResponse::class, mappedBy="student")
     */
    private $labSurveyXYQuestionResponses;

    public function __construct()
    {
        $this->enrolments = new ArrayCollection();
        $this->labSurveyXYQuestionResponses = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "STUDENT - " . $this->getUser();
    }

    public function getGuid(): ?int
    {
        return $this->guid;
    }

    public function setGuid(int $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->appuser;
    }

    public function setUser(User $user): self
    {
        $this->appuser = $user;

        return $this;
    }

    /**
     * @return Collection|Enrolment[]
     */
    public function getEnrolments(): Collection
    {
        return $this->enrolments;
    }

    public function addEnrolment(Enrolment $enrolment): self
    {
        if (!$this->enrolments->contains($enrolment)) {
            $this->enrolments[] = $enrolment;
            $enrolment->setStudent($this);
        }

        return $this;
    }

    public function removeEnrolment(Enrolment $enrolment): self
    {
        if ($this->enrolments->contains($enrolment)) {
            $this->enrolments->removeElement($enrolment);
            // set the owning side to null (unless already changed)
            if ($enrolment->getStudent() === $this) {
                $enrolment->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LabSurveyXYQuestionResponse[]
     */
    public function getLabSurveyXYQuestionResponses(): Collection
    {
        return $this->labSurveyXYQuestionResponses;
    }

    public function addLabSurveyXYQuestionResponse(LabSurveyXYQuestionResponse $labSurveyXYQuestionResponse): self
    {
        if (!$this->labSurveyXYQuestionResponses->contains($labSurveyXYQuestionResponse)) {
            $this->labSurveyXYQuestionResponses[] = $labSurveyXYQuestionResponse;
            $labSurveyXYQuestionResponse->setStudent($this);
        }

        return $this;
    }

    public function removeLabSurveyXYQuestionResponse(LabSurveyXYQuestionResponse $labSurveyXYQuestionResponse): self
    {
        if ($this->labSurveyXYQuestionResponses->contains($labSurveyXYQuestionResponse)) {
            $this->labSurveyXYQuestionResponses->removeElement($labSurveyXYQuestionResponse);
            // set the owning side to null (unless already changed)
            if ($labSurveyXYQuestionResponse->getStudent() === $this) {
                $labSurveyXYQuestionResponse->setStudent(null);
            }
        }

        return $this;
    }
}
