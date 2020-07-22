<?php

namespace App\Form\Type;

use App\Entity\SurveyQuestionResponseInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

abstract class LabSurveyXYQuestionType extends AbstractType
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $labSurveyXyQuestion = $builder->getData();
        $xyQuestion = $labSurveyXyQuestion->getXYQuestion();

        $xField = $xyQuestion->getXField();
        $yField = $xyQuestion->getYField();

        $responses = $labSurveyXyQuestion->getResponses();

        // Serialize responses as initial data

        $builder->add('dangerZones', XYQuestionDangerZoneType::class, [
            'x_label_low' => $xField->getLowLabel(),
            'x_label_high' => $xField->getHighLabel(),
            'y_label_low' => $yField->getLowLabel(),
            'y_label_high' => $yField->getHighLabel(),
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Update Danger Zones'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set that this form is bound to an SurveyQuestionResponseInterface entity
        $resolver->setDefaults([
            'data_class' => LabSurveyXYQuestionType::class,
        ]);
    }
}
