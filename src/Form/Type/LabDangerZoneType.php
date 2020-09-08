<?php

namespace App\Form\Type;

use App\Entity\Lab;
use App\Entity\SurveyQuestionResponseInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

class LabDangerZoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Uses the collection type field to render a form element for each xyQuestion
        // from the
        $builder->add('labXYQuestions', CollectionType::class, [
            'label' => 'XY Question Danger Zones',
            'help' => 'Click a region to cycle through danger zones, then click update to set.',
            'entry_type' => LabXYQuestionType::class,
            'entry_options' => [
                'label' => false
            ],
            'attr' => [
                'class' => 'flex-form-collection',
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
            'data_class' => Lab::class,
        ]);
    }
}
