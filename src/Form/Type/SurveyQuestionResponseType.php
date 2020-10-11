<?php

/*
SurveyQuestionResponseType.php
Gareth Sears - 2493194S
*/

namespace App\Form\Type;

use App\Entity\SurveyQuestionResponseInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * An abstract form type which is used by all survey questions.
 * It ensures that they have skip and submit questions. It uses the
 * abstract template method pattern to allow inserting custom form
 * elements before the buttons and mixing in custom options for subclasses.
 */
abstract class SurveyQuestionResponseType extends AbstractType
{
    const SKIP_BUTTON_NAME = 'skip';
    const SUBMIT_BUTTON_NAME = 'submit';

    /**
     * Generates a form with the appropriate submit and skip buttons.
     * Final so that classes which extend it must use the abstract method to
     * inject their fields.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    final public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Child components implement this method. It creates the body of the form.
        $builder = $this->buildFormBody($builder, $options);

        $builder->add(self::SKIP_BUTTON_NAME, SubmitType::class, [
            'label' => $options['skipText'],
            'attr' => [
                'class' => 'btn-secondary'
            ]
        ]);

        $builder->add(self::SUBMIT_BUTTON_NAME, SubmitType::class, [
            'label' => $options['submitText']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set that this form is bound to an SurveyQuestionResponseInterface entity
        $resolver->setDefaults(array_merge([
            'data_class' => SurveyQuestionResponseInterface::class,
            'skipText' => 'Skip Question',
            'submitText' => 'Next Question'
        ], $this->getOptionDefaults()));
    }

    abstract public function buildFormBody(FormBuilderInterface $builder, array $options): FormBuilderInterface;

    abstract public function getOptionDefaults(): array;
}
