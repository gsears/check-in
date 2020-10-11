<?php

/*
LabDangerZoneType.php
Gareth Sears - 2493194S
*/

namespace App\Form\Type;

use App\Entity\Lab;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Generates a danger zone form which can be bound to a Lab entity.
 */
class LabDangerZoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Renders a LabXYQuestion (sub)form for each of the labXYQuestions connected to the lab entity.
        $builder->add('labXYQuestions', CollectionType::class, [
            // Gives the collection one title
            'label' => 'XY Question Danger Zones',
            'help' => 'Click a region to cycle through danger zones, then click update to set.',
            'entry_type' => LabXYQuestionType::class,
            'entry_options' => [
                'label' => false
            ],
            // Add custom flex styling to the children.
            'attr' => [
                'class' => 'flex-form-collection',
            ]
        ]);

        // Submit button for POSTing the form.
        $builder->add('submit', SubmitType::class, [
            'label' => 'Update Danger Zones'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Bind this form type to a Lab entity.
            'data_class' => Lab::class,
        ]);
    }
}
