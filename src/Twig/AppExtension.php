<?php

/*
AppExtension.php
Gareth Sears - 2493194S
*/

namespace App\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use App\Containers\Risk\RiskInterface;

/**
 * This class defines custom extensions for twig templates.
 * 
 * Specifically, it is used to define the renderRisk() twig function
 * which provides a similar API to symfony forms for risk containers.
 */
class AppExtension extends AbstractExtension
{
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('risk', [$this, 'renderRisk'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * As rendering survey responses and their associated risks depends on the type of
     * risk object and question, a custom Twig function 'risk' is defined. This renders
     * the object based on the twig template which it references as well as its custom
     * options.
     *
     * This pattern follows how Symfony renders its form objects. See:
     * https://symfony.com/doc/current/form/form_customization.html#form-rendering-variables
     * for what inspired it.
     *
     *
     * @param RiskInterface $risk
     * @return void
     */
    public function renderRisk(RiskInterface $risk)
    {
        return $this->twig->render(
            $risk->getTwigTemplate(),
            array_merge($risk->getDefaultContext(), $risk->getContext())
        );
    }
}
