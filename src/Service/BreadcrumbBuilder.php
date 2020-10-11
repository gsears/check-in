<?php

/*
BreadcrumbBuilder.php
Gareth Sears - 2493194S
*/

namespace App\Service;

use App\Containers\Breadcrumb;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Implements the builder pattern to assist with creating a consumable 'breadcrumb' context array
 * for use inside twig templates (see base.html.twig's Breadcrumb macro). Useful for conditional
 * breadcrumb generation based on user permissions and to provide a clean API.
 */
class BreadcrumbBuilder
{
    private $router;
    private $breadcrumbs = [];

    /**
     * Injects the Router into the class so it can be used to generate URLs from route aliases.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function add($name, $href = null): self
    {
        $this->breadcrumbs[] = new Breadcrumb($name, $href);
        return $this;
    }

    public function addRoute($name, $routeAlias, $parameters = [],  int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): self
    {
        $href = $this->router->generate($routeAlias, $parameters, $referenceType);
        $this->add($name, $href);
        return $this;
    }

    public function build(): array
    {
        return $this->breadcrumbs;
    }
}
