<?php

namespace App\Form\Type;

use App\Entity\LabSurveyResponse;
use App\Entity\LabSurveyXYQuestion;
use App\Entity\LabSurveyXYQuestionResponse;
use App\Entity\XYQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

class XYQuestionType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Doesn't bind to any values, just a view component.
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
            'data_class' => XYQuestion::class,
            'id' => null,
            'values' => null,
            'x_label_low' => null,
            'x_label_high' => null,
            'y_label_low' => null,
            'y_label_high' => null,
            'x_field_id' => null,
            'y_field_id' => null,
        ]);

        // Validate this form by requiring all of the below
        $resolver
            ->setAllowedTypes('id', 'string')
            ->setAllowedTypes('values', 'string')
            ->setAllowedTypes('x_label_low', 'string')
            ->setAllowedTypes('x_label_high', 'string')
            ->setAllowedTypes('y_label_low', 'string')
            ->setAllowedTypes('y_label_high', 'string')
            // Add references to any hidden fields in the parent form
            // to populate using javascript.
            ->setAllowedTypes('x_field_id', ['string', null])
            ->setAllowedTypes('y_field_id', ['string', null]);
    }
}
