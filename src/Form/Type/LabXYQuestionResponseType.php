<?php

/*
LabXYQuestionResponseType.php
Gareth Sears - 2493194S
*/

namespace App\Form\Type;

use App\Form\Type\XYCoordinatesType;
use App\Entity\LabXYQuestionResponse;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Generates a form for giving an XY response which can be bound to a LabXYQuestionResponse entity.
 */
class LabXYQuestionResponseType extends SurveyQuestionResponseType
{
    public function buildFormBody(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        // Get the XYQuestionResponse bound to this form.
        $xyQuestionResponse = $builder->getData();

        // Get the question information for displaying.
        $xyQuestion = $xyQuestionResponse->getLabXYQuestion()->getXYQuestion();
        $xField = $xyQuestion->getXField();
        $yField = $xyQuestion->getYField();

        // Bind an XYCoordinatesType (sub)form to the coordinates property of the
        // LabXYQuestionResponse bound to this form. Set labels.
        $builder
            ->add('coordinates', XYCoordinatesType::class, [
                'label' => $xyQuestion->getQuestionText(),
                'help' => sprintf(
                    'Click on the grid to select a response. The x axis represents %s. The y axis represents %s.',
                    $xField->getName(),
                    $yField->getName()
                ),
                'x_label_low' => $xField->getLowLabel(),
                'x_label_high' => $xField->getHighLabel(),
                'y_label_low' => $yField->getLowLabel(),
                'y_label_high' => $yField->getHighLabel(),
                'not_blank' => true
            ]);

        return $builder;
    }

    public function getOptionDefaults(): array
    {
        return [
            // Bind this form type to a LabXYQuestionResponse entity.
            'data_class' => LabXYQuestionResponse::class,
        ];
    }
}
