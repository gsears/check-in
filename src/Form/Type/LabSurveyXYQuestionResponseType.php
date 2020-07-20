<?php

namespace App\Form\Type;

use App\Entity\LabSurveyResponse;
use App\Entity\LabSurveyXYQuestion;
use App\Entity\LabSurveyXYQuestionResponse;
use App\Entity\XYQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use XYCoordinates;

class LabSurveyXYQuestionResponseType extends AbstractType
{
    private $xyQuestion;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Get the data so we can query it for it's XY Question
        $xyQuestionResponse = $builder->getData();

        // https://stackoverflow.com/questions/21862168/calling-builder-getdata-from-within-a-nested-form-always-returns-null
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function ($event) {
            $builder = $event->getForm();
            $xyQuestionResponse = $event->getData();

            // If the object does not exist in the database
            $xValue = $xyQuestionResponse->getXValue();
            $yValue = $xyQuestionResponse->getYValue();

            if($xValue && $yValue) {
                $initial = new XYCoordinates($xValue, $yValue);
            } else {
                $initial = null;
            }

            $xyQuestion = $xyQuestionResponse->getLabSurveyXYQuestion()->getXYQuestion();
            $xField = $xyQuestion->getXField();
            $yField = $xyQuestion->getYField();

            // Add hidden types for XY responses. These will be filled via the js component.
            $builder
                // Do not map the xy form component to the entity.
                ->add($xyQuestion->getName(), XYQuestionType::class, [
                    'mapped' => false,
                    'id' => strval($xyQuestion->getId()),
                    'initial_values' => $initial,
                    'x_label_low' => $xField->getLowLabel(),
                    'x_label_high' => $xField->getHighLabel(),
                    'y_label_low' => $yField->getLowLabel(),
                    'y_label_high' => $yField->getHighLabel(),

                ])
                ->add('xValue', HiddenType::class)
                ->add('yValue', HiddenType::class);
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set that this form is bound to an LabSurveyResponse entity
        $resolver->setDefaults([
            'data_class' => LabSurveyXYQuestionResponse::class,
        ]);
    }
}
