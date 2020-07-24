<?php

namespace App\Form\Type;

use App\Entity\LabResponse;
use App\Entity\LabXYQuestion;
use App\Entity\LabXYQuestionResponse;
use App\Entity\XYCoordinates;
use App\Entity\XYQuestion;
use App\Entity\LabXYQuestionDangerZone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Implements DataMapperInterface to return an XYCoordinates object.
 * https://symfony.com/doc/current/form/data_mappers.html
 */
class LabXYQuestionDangerZoneType extends AbstractXYComponentType
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
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

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['coordinates'] = $options['coordinates'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        // Allow coordinates to be passed in to populate the xy component.
        $resolver->setDefault('coordinates', null);
        // Should be a json array.
        $resolver->setAllowedTypes('coordinates', 'string');
    }

    public function provideJsonContent($viewData): ?string
    {
        if (!$viewData) {
            return null;
        }

        dump($viewData);
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
