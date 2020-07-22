<?php

namespace App\Form\Type;

use App\Entity\LabSurveyResponse;
use App\Entity\LabSurveyXYQuestion;
use App\Entity\LabSurveyXYQuestionResponse;
use App\Entity\XYCoordinates;
use App\Entity\XYQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Implements DataMapperInterface to return an XYCoordinates object.
 * https://symfony.com/doc/current/form/data_mappers.html
 */
class XYCoordinatesType extends AbstractXYComponentType
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function provideJsonContent($viewData): string
    {
        if (!$viewData) {
            return "[]";
        }

        // invalid data type
        if (!$viewData instanceof XYCoordinates) {
            throw new UnexpectedTypeException($viewData, XYCoordinates::class);
        }

        dump($viewData);

        $array = [$viewData];

        dump($array);
        // Convert the XYCoordinate object into a json array for consumption
        // in the javascript component.
        $string = $this->serializer->serialize($array, 'json');
        dump($string);
        return $string;
    }

    public function consumeJsonContent($jsonContent)
    {
        $resultArray = $this->serializer->deserialize($jsonContent, XYCoordinates::class . '[]', 'json');
        if (count($resultArray) === 0) {
            return null;
        } else {
            return $resultArray[0];
        }
    }
}
