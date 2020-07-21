<?php

namespace App\Form\Type;

use App\Entity\LabSurveyResponse;
use App\Entity\LabSurveyXYQuestion;
use App\Entity\LabSurveyXYQuestionResponse;
use App\Entity\XYQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Implements DataMapperInterface to return an XYCoordinates object.
 * https://symfony.com/doc/current/form/data_mappers.html
 */
class XYQuestionType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Hidden forms to store the XY values and bind them.
        $builder
            ->add('xValue', HiddenType::class)
            ->add('yValue', HiddenType::class);
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
            'x_label_low' => null,
            'x_label_high' => null,
            'y_label_low' => null,
            'y_label_high' => null,
        ]);

        // Validate this form by requiring all of the below
        $resolver
            ->setAllowedTypes('x_label_low', 'string')
            ->setAllowedTypes('x_label_high', 'string')
            ->setAllowedTypes('y_label_low', 'string')
            ->setAllowedTypes('y_label_high', 'string');
    }


    public function mapDataToForms($viewData, iterable $forms)
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof XYCoordinates) {
            throw new UnexpectedTypeException($viewData, XYCoordinates::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['xValue']->setData($viewData->getX());
        $forms['yValue']->setData($viewData->getY());
    }

    public function mapFormsToData(iterable $forms, &$viewData)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $viewData = new XYCoordinates(
            $forms['xValue']->getData(),
            $forms['yValue']->getData(),
        );
    }
}
