<?php

namespace App\Entity;

use App\Repository\SentimentQuestionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SentimentQuestionRepository::class)
 */
class SentimentQuestion implements QuestionInterface
{
    const POSITIVE = "Positive";
    const NEUTRAL = "Neutral";
    const NEGATIVE = "Negative";

    public static function isValidClassification(string $classification)
    {
        return in_array($classification, [self::POSITIVE, self::NEUTRAL, self::NEGATIVE]);
    }


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
}
