<?php

/*
LabXYQuestionDangerZoneType.php
Gareth Sears - 2493194S
*/

namespace App\Form\Type;

use Symfony\Component\Form\FormView;
use App\Entity\LabXYQuestionDangerZone;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

/**
 * Generates a form for setting danger zones which can be bound to a LabXYQuestionDangerZone entity.
 *
 * This extends the AbstractXYComponentType, as we'll be using our Vue XY Component as the UI.
 */
class LabXYQuestionDangerZoneType extends AbstractXYComponentType
{
    private $serializer;

    public function __construct()
    {
        // This uses a custom normalizer which allows us to access the private properties
        // of the object. This way, we can bypass the 'setBound' method, which can cause
        // some problems when denormalizing from JSON. Array denormalizer helps us map the result to
        // a collection.

        // TODO: Write a custom denormalizer which handles setBound.
        $this->serializer = new Serializer(
            [new PropertyNormalizer(), new ArrayDenormalizer()],
            [new JsonEncoder()]
        );
    }

    /**
     * This defines the twig template fragment used to generate the html for this form
     * component in /templates/form/custom_types.html.twig . We define a our own
     * fragment to hook it up to the javascript component.
     *
     * @return void
     */
    public function getBlockPrefix()
    {
        return 'xy_danger_zones';
    }

    /**
     * Passes the required variables into the twig template context for consumption
     * in the JS component.
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['coordinates'] = $options['coordinates'];
        $view->vars['read_only'] = $options['read_only'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        // Allow coordinates to be passed in to populate the xy component.
        $resolver->setDefault('coordinates', null);
        $resolver->setDefault('read_only', false);
        // Should be a json array.
        $resolver->setAllowedTypes('coordinates', 'string');
        $resolver->setAllowedTypes('read_only', 'boolean');
    }

    public function provideJsonContent($viewData): ?string
    {
        if (!$viewData) {
            return null;
        }

        // Serialise the XYQuestionDangerZone collection, ignoring the labXYQuestion field,
        // as this won't be set by the javascript component and would result in circular
        // serialization.
        $out = $this->serializer->serialize(
            $viewData,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['labXYQuestion']]
        );

        return $out;
    }

    public function consumeJsonContent($jsonContent)
    {
        if (null === $jsonContent) {
            return null;
        }

        // Converts a json string into XYQuestionDangerZone entities.
        return $this->serializer->deserialize($jsonContent, LabXYQuestionDangerZone::class . '[]', 'json');
    }
}
