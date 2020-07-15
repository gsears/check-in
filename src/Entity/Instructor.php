<?php

namespace App\Entity;

use App\Repository\InstructorRepository;
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

    public function __toString(): string
    {
        return "INSTRUCTOR - " . $this->getUser();
    }
}
