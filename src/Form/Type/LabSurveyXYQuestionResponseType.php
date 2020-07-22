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
use Symfony\Component\Validator\Constraints\NotBlank;

class LabSurveyXYQuestionResponseType extends SurveyQuestionResponseType
{
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
                'constraints' => new NotBlank(),
                'label' => $xyQuestion->getQuestionText(),
                'help' => sprintf(
                    'Click on the grid to select a response. The x axis represents %s. The y axis represents %s.',
                    $xyQuestion->getXField()->getName(),
                    $xyQuestion->getYField()->getName()
                ),
                'x_label_low' => $xField->getLowLabel(),
                'x_label_high' => $xField->getHighLabel(),
                'y_label_low' => $yField->getLowLabel(),
                'y_label_high' => $yField->getHighLabel(),
            ]);

        return $builder;
    }
}
