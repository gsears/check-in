<?php

/*
AffectiveField.php
Gareth Sears - 2493194S
*/

namespace App\Entity;

use App\Repository\AffectiveFieldRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AffectiveFieldRepository::class)
 */
class AffectiveField
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
    private $lowLabel;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $highLabel;

    public function __toString()
    {
        return sprintf(
            "Affective Field: %s <- %s -> %s\n",
            $this->getLowLabel(),
            $this->getName(),
            $this->getHighLabel()
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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLowLabel(): ?string
    {
        return $this->lowLabel;
    }

    public function setLowLabel(string $lowLabel): self
    {
        $this->lowLabel = $lowLabel;

        return $this;
    }

    public function getHighLabel(): ?string
    {
        return $this->highLabel;
    }

    public function setHighLabel(string $highLabel): self
    {
        $this->highLabel = $highLabel;

        return $this;
    }
}
