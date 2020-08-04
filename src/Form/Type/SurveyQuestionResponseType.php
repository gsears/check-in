<?php

namespace App\Form\Type;

use App\Entity\SurveyQuestionResponseInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class SurveyQuestionResponseType extends AbstractType
{

    const SKIP_BUTTON_NAME = 'skip';

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
            'label' => 'Skip Question',
            'attr' => [
                'class' => 'btn-secondary'
            ]
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Submit Response'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set that this form is bound to an SurveyQuestionResponseInterface entity
        $resolver->setDefaults([
            'data_class' => SurveyQuestionResponseInterface::class,
        ]);
    }

    abstract public function buildFormBody(FormBuilderInterface $builder, array $options): FormBuilderInterface;
}
