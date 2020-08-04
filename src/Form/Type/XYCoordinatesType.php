<?php

/*
XYCoordinatesType.php
Gareth Sears - 2493194S
*/

namespace App\Form\Type;

use App\Containers\XYCoordinates;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * TODO: Document
 */
class XYCoordinatesType extends AbstractXYComponentType
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
        return 'xy_coordinates';
    }

    public function provideJsonContent($viewData): ?string
    {
        if (!$viewData) {
            return null;
        }

        // invalid data type
        if (!$viewData instanceof XYCoordinates) {
            throw new UnexpectedTypeException($viewData, XYCoordinates::class);
        }

        // Convert the XYCoordinate object into a json array for consumption
        // in the javascript component.
        return $this->serializer->serialize([$viewData], 'json');
    }

    public function consumeJsonContent($jsonContent)
    {
        if (null === $jsonContent) {
            return null;
        }

        $resultArray = $this->serializer->deserialize($jsonContent, XYCoordinates::class . '[]', 'json');

        if (count($resultArray) === 0) {
            return null;
        } else {
            return $resultArray[0];
        }
    }
}
