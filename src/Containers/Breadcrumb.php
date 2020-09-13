<?php

namespace App\Containers;

class Breadcrumb
{
    private $name;
    private $href;

    public function __construct(string $name, string $href = null)
    {
        $this->name = $name;
        $this->href = $href;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getHref()
    {
        return $this->href;
    }
}
