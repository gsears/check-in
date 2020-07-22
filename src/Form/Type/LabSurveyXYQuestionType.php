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
use App\Entity\XYQuestionDangerZone;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Constraints\NotBlank;

class LabSurveyXYQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $labSurveyXYQuestion = $event->getData();

            $xyQuestion = $labSurveyXYQuestion->getXYQuestion();
            $xField = $xyQuestion->getXField();
            $yField = $xyQuestion->getYField();

            $form = $event->getForm();

            dump("here");
            $form
                // Do not map the xy form component to the entity.
                ->add('dangerZones', XYQuestionDangerZoneType::class, [
                    'label' => $xyQuestion->getName(),
                    'x_label_low' => $xField->getLowLabel(),
                    'x_label_high' => $xField->getHighLabel(),
                    'y_label_low' => $yField->getLowLabel(),
                    'y_label_high' => $yField->getHighLabel(),
                    // Can be blank (no danger zones)
                    'not_blank' => false
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set that this form is bound to an SurveyQuestionResponseInterface entity
        $resolver->setDefaults([
            'data_class' => LabSurveyXYQuestion::class,
        ]);
    }
}
