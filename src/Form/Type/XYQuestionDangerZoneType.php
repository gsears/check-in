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
class XYQuestionDangerZoneType extends AbstractXYType implements DataMapperInterface
{
    private $serializer;

    // Inject the serializer
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Hidden form to store the json string from the jscomponent
        $builder
            ->add('json', HiddenType::class)
            // Configure to create dangerzones on submit
            ->setDataMapper($this);
    }

    public function mapDataToForms($viewData, iterable $forms)
    {

        dump($viewData);
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // serialize data, ignoring the labSurveyQuestion field.
        $jsonContent = $this->serializer->serialize(
            $viewData,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['labSurveyQuestion']]
        );

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['json']->setData($jsonContent);
    }

    public function mapFormsToData(iterable $forms, &$viewData)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        $jsonContent = $forms['json']->getData();

        dump($jsonContent);
        $viewData = $this->serializer->deserialize($jsonContent, XYQuestionDangerZone::class, 'json');
    }
}
