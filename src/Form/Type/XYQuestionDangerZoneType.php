<?php

namespace App\Form\Type;

use App\Entity\LabSurveyResponse;
use App\Entity\LabSurveyXYQuestion;
use App\Entity\LabSurveyXYQuestionResponse;
use App\Entity\XYCoordinates;
use App\Entity\XYQuestion;
use App\Entity\XYQuestionDangerZone;
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
class XYQuestionDangerZoneType extends AbstractXYComponentType
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function provideJsonContent($viewData): ?string
    {
        if (!$viewData) {
            return null;
        }

        // Serialise the XYQuestionDangerZone collection, ignoring the labSurveyQuestion field,
        // as this won't be set by the javascript component.
        return $this->serializer->serialize(
            $viewData,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['labSurveyQuestion']]
        );
    }

    public function consumeJsonContent($jsonContent)
    {
        if (null === $jsonContent) {
            return null;
        }

        // Converts a json string into XYQuestionDangerZone entities.
        return $this->serializer->deserialize($jsonContent, XYQuestionDangerZone::class . '[]', 'json');
    }
}
