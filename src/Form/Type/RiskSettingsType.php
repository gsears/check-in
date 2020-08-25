<?php

namespace App\Form\Type;

use App\Entity\CourseInstance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiskSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $name = $builder->getName();
        $builder
            ->add('riskThreshold', NumberType::class, [
                'label' => 'Set risk threshold %',
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100
                ],
            ])
            ->add('riskConsecutiveLabCount', NumberType::class, [
                'label' => 'For how many consecutive labs?',
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100
                ],
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CourseInstance::class,
        ]);
    }
}
