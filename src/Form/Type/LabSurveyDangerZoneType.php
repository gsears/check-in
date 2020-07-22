<?php

namespace App\Form\Type;

use App\Entity\LabSurvey;
use App\Entity\SurveyQuestionResponseInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

class LabSurveyDangerZoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Uses the collection type field to render a form element for each xyQuestion
        // from the
        $builder->add('xyQuestions', CollectionType::class, [
            'label' => 'XY Questions',
            'help' => 'Click on the regions to set the warning level, then click update.',
            'entry_type' => LabSurveyXYQuestionType::class,
            'entry_options' => [
                'label' => false
            ]
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Update Danger Zones'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set that this form is bound to an SurveyQuestionResponseInterface entity
        $resolver->setDefaults([
            'data_class' => LabSurvey::class,
        ]);
    }
}
