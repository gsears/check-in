<?php

namespace App\Entity;

use App\Repository\StudentRepository;
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

    public function __toString(): string
    {
        return $this->getUser();
    }
}
