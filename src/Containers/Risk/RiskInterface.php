<?php

namespace App\Containers\Risk;

interface RiskInterface
{
    public function getTwigTemplate(): string;

    public function getDefaultContext(): array;

    public function getContext(): array;
}
