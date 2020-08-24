<?php

/*
RiskFlagType.php
Gareth Sears - 2493194S
*/

namespace App\Form\Type;

use App\Entity\Enrolment;
use App\Security\Roles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RiskFlagType extends AbstractType
{
    const MANUAL_FLAG_BUTTON = 'manual';
    const DESCRIPTION_INPUT = 'description';
    const REMOVE_FLAG_BUTTON = 'remove';

    // Types
    const USER_ROLES = 'user_roles';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var Enrolment
         */
        $enrolment = $builder->getData();
        $flag = $enrolment->getRiskFlag();
        $roles = $options[self::USER_ROLES];

        if (is_null($flag)) {
            $builder
                ->add(self::DESCRIPTION_INPUT, TextType::class, [
                    'mapped' => false,
                    'label' => '(Optional) description',
                    'required' => false
                ])
                ->add(self::MANUAL_FLAG_BUTTON, SubmitType::class, [
                    'label' => 'Flag for support',
                    'attr' => [
                        'class' => 'btn btn-danger'
                    ]
                ]);
        } else {
            if (!in_array(Roles::STUDENT, $roles) || $flag === Enrolment::FLAG_BY_STUDENT) {
                $builder->add(self::REMOVE_FLAG_BUTTON, SubmitType::class, [
                    'label' => 'Remove support flag',
                    'attr' => [
                        'class' => 'btn btn-outline-danger'
                    ]
                ]);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Enrolment::class,
            self::USER_ROLES => null,
        ]);

        $resolver->setAllowedTypes(self::USER_ROLES, ['string[]']);
    }
}
