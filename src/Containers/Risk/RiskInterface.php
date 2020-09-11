<?php

/*
RiskInterface.php
Gareth Sears - 2493194S
*/

namespace App\Containers\Risk;

/**
 * Defines a risk wrapper which can be rendered as a view.
 *
 * This is used to create specific html when a user clicks on a risk 'traffic light'
 * button. For example, an XY question has different attributes than a sentiment question,
 * thus would need to be rendered differently.
 */
interface RiskInterface
{
    /**
     * A method which returns the Twig view template associated with this RiskInterface.
     *
     * @return string
     */
    public function getTwigTemplate(): string;

    /**
     * Defines the default context dictionary passed to the Twig template. This is most
     * likely to be used by an abstract class to define standard 'risk question' defaults.
     *
     * @return array
     */
    public function getDefaultContext(): array;

    /**
     * Allows for additional context parameters to be mixed in to the defaults. This is
     * most likely to be used by concrete implementations of an abstract risk class.
     *
     * @return array
     */
    public function getContext(): array;
}
