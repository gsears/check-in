<?php

namespace App\Form\Type;

use App\Entity\LabSurveyResponse;
use App\Entity\LabSurveyXYQuestion;
use App\Entity\XYQuestion;
use Symfony\Component\Form\AbstractType;;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class XYQuestionType extends AbstractType
{
    private $surveyResponse;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->surveyResponse = $builder->getData();
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Lazy merge to grab all options as vars as there are no conditions
        $view->vars = array_merge($view->vars, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set that this form is bound to an LabSurveyResponse entity
        $resolver->setDefaults([
            'data_class' => XYQuestionType::class,
            'question_text' => null,
            'x_label_low' => null,
            'x_label_high' => null,
            'y_label_low' => null,
            'y_label_high' => null,
        ]);

        // Validate this form by requiring all of the below
        $resolver
            ->setAllowedTypes('question_text', 'string')
            ->setAllowedTypes('x_label_low', 'string')
            ->setAllowedTypes('x_label_high', 'string')
            ->setAllowedTypes('y_label_low', 'string')
            ->setAllowedTypes('y_label_high', 'string');
    }
}
