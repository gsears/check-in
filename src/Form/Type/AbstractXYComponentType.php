<?php

namespace App\Form\Type;

use App\Entity\LabSurveyResponse;
use App\Entity\LabSurveyXYQuestion;
use App\Entity\LabSurveyXYQuestionResponse;
use App\Entity\XYCoordinates;
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
abstract class AbstractXYComponentType extends AbstractType implements DataMapperInterface
{
    private $initialData;

    public function __construct()
    {
        $this->initialData = $this->setInitialData();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Hidden forms to store the XY values and bind them.
        $builder
            ->add('jsonContent', HiddenType::class)
            // Configure to create coordinates on submit
            ->setDataMapper($this);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_merge($view->vars, $options);
        dump("building initial data");
        // Lazy merge to grab all options as vars as there are no conditions
        $view->vars['initial_data'] = $this->initialData;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // 'empty-data' null prevents automatic creation of the item, as we want to
        // control this from our data mapper
        $resolver->setDefault('empty_data', null);

        $resolver->setDefaults([
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
        $jsonContent = $this->provideJsonContent($viewData);
        $initialData = $this->setInitialData();

        dump("setting initial data");
        $this->initialData = $initialData ? $initialData : $jsonContent;

        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['jsonContent']->setData($jsonContent);
    }

    public function mapFormsToData(iterable $forms, &$viewData)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        $jsonContent = $forms['jsonContent']->getData();

        $viewData = $this->consumeJsonContent($jsonContent);
    }

    final public function getBlockPrefix()
    {
        return 'xy_component';
    }

    // Must return null or the object required
    abstract protected function consumeJsonContent($jsonContent);

    abstract protected function provideJsonContent($viewData): string;

    // Sets the initial data in the json component to be
    protected function setInitialData(): ?string
    {
        return null;
    }
}
