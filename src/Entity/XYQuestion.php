<?php

/*
XYQuestion.php
Gareth Sears - 2493194S
*/

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\XYQuestionRepository;

/**
 * @ORM\Entity(repositoryClass=XYQuestionRepository::class)
 */
class XYQuestion implements QuestionInterface
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
    private $questionText;

    /**
     * @ORM\ManyToOne(targetEntity=AffectiveField::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $xField;

    /**
     * @ORM\ManyToOne(targetEntity=AffectiveField::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $yField;

    public function __toString()
    {
        return sprintf(
            "XY Question: %s\n%s\nx: %sy: %s\n",
            $this->getName(),
            $this->getQuestionText(),
            $this->getXField(),
            $this->getYField()
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

    public function getQuestionText(): ?string
    {
        return $this->questionText;
    }

    public function setQuestionText(string $questionText): self
    {
        $this->questionText = $questionText;

        return $this;
    }

    public function getXField(): ?AffectiveField
    {
        return $this->xField;
    }

    public function setXField(?AffectiveField $xField): self
    {
        $this->xField = $xField;

        return $this;
    }

    public function getYField(): ?AffectiveField
    {
        return $this->yField;
    }

    public function setYField(?AffectiveField $yField): self
    {
        $this->yField = $yField;

        return $this;
    }
}
