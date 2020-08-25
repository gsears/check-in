<?php

namespace App\Entity;

/**
 * An interface to help typecheck question types
 */
interface QuestionInterface
{
    public function getName(): ?string;

    public function getQuestionText(): ?string;
}
