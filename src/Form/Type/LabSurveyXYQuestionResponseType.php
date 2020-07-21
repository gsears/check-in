<?php

namespace App\Form\Type;

use App\Entity\XYQuestion;
use App\Form\Type\XYCoordinates;
use App\Entity\LabSurveyResponse;
use App\Form\Type\XYCoordinatesType;
use App\Entity\LabSurveyXYQuestion;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use App\Entity\LabSurveyXYQuestionResponse;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;


class LabSurveyXYQuestionResponseType extends SurveyQuestionResponseType
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function buildFormBody(FormBuilderInterface $builder): FormBuilderInterface
    {
        // Get the data so we can query it for its XY Question
        $xyQuestionResponse = $builder->getData();

        $xyQuestion = $xyQuestionResponse->getLabSurveyXYQuestion()->getXYQuestion();
        $xField = $xyQuestion->getXField();
        $yField = $xyQuestion->getYField();

        // Add hidden types for XY responses. These will be filled via the js component.
        $builder
            // Do not map the xy form component to the entity.
            ->add('coordinates', XYCoordinatesType::class, [
                'label' => $xyQuestion->getName(),
                'x_label_low' => $xField->getLowLabel(),
                'x_label_high' => $xField->getHighLabel(),
                'y_label_low' => $yField->getLowLabel(),
                'y_label_high' => $yField->getHighLabel(),
            ]);

        return $builder;
    }
}
