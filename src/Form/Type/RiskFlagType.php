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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Generates a form for setting risk flags which is bound to an
 * Enrolment entity.
 */
class RiskFlagType extends AbstractType
{
    const MANUAL_FLAG_BUTTON = 'manual';
    const DESCRIPTION_INPUT = 'description';
    const REMOVE_FLAG_BUTTON = 'remove';

    const USER_ROLES = 'user_roles';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * Get the enrolment bound to this form.
         *
         * @var Enrolment
         */
        $enrolment = $builder->getData();

        // Get the risk flag on the enrolment.
        $flag = $enrolment->getRiskFlag();

        // Get the role of the user using the form (instructor / student)
        $roles = $options[self::USER_ROLES];

        // If no flag is set, create an 'add flag' form.
        if (is_null($flag)) {
            $builder
                ->add(self::DESCRIPTION_INPUT, TextareaType::class, [
                    'mapped' => false,
                    'label' => 'Add an optional description',
                    'required' => false
                ])
                ->add(self::MANUAL_FLAG_BUTTON, SubmitType::class, [
                    'label' => 'Flag for support',
                    'attr' => [
                        'class' => 'btn btn-danger'
                    ]
                ]);
            // If a flag is set, add a 'remove flag' form if the user has permissions to remove it.
            // TODO: Split this form into a seperate 'remove flag' form and make controller responsible
            // for correct choice.
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
            // Bind this form type to a Enrolment entity.
            'data_class' => Enrolment::class,
            self::USER_ROLES => null,
        ]);

        $resolver->setAllowedTypes(self::USER_ROLES, ['string[]']);
    }
}
