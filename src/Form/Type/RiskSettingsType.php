<?php

namespace App\Form\Type;

use App\Entity\CourseInstance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiskSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('riskThreshold', RangeType::class, [
                'label' => 'Risk Threshold %',
                'attr' => [
                    'min' => 0,
                    'max' => 100
                ],
                'help' => 'Slide to adjust the risk threshold for automatically detecting students at risk.',
            ])
            ->add('riskConsecutiveLabCount')
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CourseInstance::class,
        ]);
    }
}
