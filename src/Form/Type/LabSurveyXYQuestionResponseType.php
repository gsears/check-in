<?php

namespace App\Form\Type;

use App\Entity\XYQuestion;
use App\Form\Type\XYCoordinates;
use App\Entity\LabSurveyResponse;
use App\Form\Type\XYQuestionType;
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


class LabSurveyXYQuestionResponseType extends AbstractType
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Get the data so we can query it for it's XY Question
        $xyQuestionResponse = $builder->getData();

        // https://stackoverflow.com/questions/21862168/calling-builder-getdata-from-within-a-nested-form-always-returns-null
        // https://symfony.com/doc/current/form/events.html
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function ($event) {
            $builder = $event->getForm();
            $xyQuestionResponse = $event->getData();

            $xyQuestion = $xyQuestionResponse->getLabSurveyXYQuestion()->getXYQuestion();
            $xField = $xyQuestion->getXField();
            $yField = $xyQuestion->getYField();

            // Add hidden types for XY responses. These will be filled via the js component.
            $builder
                // Do not map the xy form component to the entity.
                ->add('coordinates', XYQuestionType::class, [
                    'x_label_low' => $xField->getLowLabel(),
                    'x_label_high' => $xField->getHighLabel(),
                    'y_label_low' => $yField->getLowLabel(),
                    'y_label_high' => $yField->getHighLabel(),
                ]);
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
