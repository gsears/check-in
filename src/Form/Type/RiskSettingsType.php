<?php

/*
RiskSettingsType.php
Gareth Sears - 2493194S
*/

namespace App\Form\Type;

use App\Entity\CourseInstance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Generates a form for setting risk thresholds which is bound to an
 * CourseInstance entity.
 */
class RiskSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Bind a number type to the risk threshold on the CourseInstance
        $builder
            ->add('riskThreshold', NumberType::class, [
                'label' => 'Set risk threshold %',
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100
                ],
            ])
            // Bind a number type to the riskConsecutiveLabCount attribute on the
            // CourseInstance
            ->add('riskConsecutiveLabCount', NumberType::class, [
                'label' => 'For how many consecutive labs?',
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100
                ],
            ])
            // Add a submit button
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Bind this form type to a CourseInstance entity.
            'data_class' => CourseInstance::class,
        ]);
    }
}
