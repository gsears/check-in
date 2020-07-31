<?php

namespace App\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;;

/**
 * Implements DataMapperInterface to return an XYCoordinates object.
 * https://symfony.com/doc/current/form/data_mappers.html
 */
abstract class AbstractXYComponentType extends AbstractType implements DataMapperInterface
{
    const EMPTY_ERROR_MESSAGE = "You must select a point on the graph.";

    /**
     * Dynamically builds the form. In this case, we use symfony's form builder to create
     * a hidden form field to which we map the json provided by our Vue.js xy component in
     *
     *
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $opts = [];

        // Add constraints to the field for form validation if 'not_blank' selected.
        // Blank can be a 'null' value or an empty json array.
        if ($options['not_blank']) {
            $opts = [
                'constraints' => [
                    new NotBlank([
                        'message' => self::EMPTY_ERROR_MESSAGE
                    ]),
                    new NotEqualTo([
                        'value' => "[]",
                        'message' => self::EMPTY_ERROR_MESSAGE
                    ]),
                ]
            ];
        }
        // Hidden forms to store the XY values and bind them.
        $builder
            ->add('jsonContent', HiddenType::class, $opts)
            // Configure to create coordinates on submit
            ->setDataMapper($this);
    }

    /**
     * Here we pass variables into our twig template.
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Lazy merge to grab all options in ::configureOptions as vars.
        $view->vars = array_merge($view->vars, $options);

        // If the user defines different initial data (for example, the XYComponent is
        // used to set dangerzones, but we want to populate it with survey results too),
        // we do it here.
        $view->vars['initial_data'] = $options['initial_data'];
    }

    /**
     * This method allows us to set the expected options to be passed into this form
     * component. In this case, we want the XY labels.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // Set default options for the class.
        $resolver->setDefaults([
            // Empty data allows us to set the value in the hidden field manually.
            'empty_data' => null,
            'x_label_low' => null,
            'x_label_high' => null,
            'y_label_low' => null,
            'y_label_high' => null,
            'not_blank' => true,
            'initial_data' => null,
            'cell_size' => 1,
        ]);

        // Type check the options passed in.
        $resolver
            ->setAllowedTypes('x_label_low', 'string')
            ->setAllowedTypes('x_label_high', 'string')
            ->setAllowedTypes('y_label_low', 'string')
            ->setAllowedTypes('y_label_high', 'string')
            ->setAllowedTypes('not_blank', 'boolean')
            ->setAllowedTypes('initial_data', ['null', 'string']);
    }

    /**
     * When we use this component, it will be bound to an entity type.
     * We need to transform this into json to put it in the hidden field
     * so our javascript component can work with it. This is done using the
     * abstract method ::provideJsonContent, which is passed the entity to
     * transform.
     */
    public function mapDataToForms($viewData, iterable $forms)
    {
        // This is the abstract method which passes the subclass
        // the entity to be transformed into json for our hidden field.
        $jsonContent = $this->provideJsonContent($viewData);

        // If null is provided, set to empty array
        $jsonContent = $jsonContent ? $jsonContent : '[]';

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['jsonContent']->setData($jsonContent);
    }

    /**
     * As we're storing the json response from the javascript component in the
     * hidden field, we need to transform it into the correct entity types for
     * our database. This is done here, using abstract methods which allow subclasses
     * to grab our json and turn it into something our database recognises.
     */
    public function mapFormsToData(iterable $forms, &$viewData)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        $jsonContent = $forms['jsonContent']->getData();

        // This is the abstract method which passes the subclass the
        // json in the hidden field.
        $data = $this->consumeJsonContent($jsonContent);

        // If data exists, set it.
        if ($data) {
            $viewData = $data;
        }
    }

    /**
     * @param [type] $jsonContent The json from the javascript component to convert.
     * @return object|null The entity / collection which is mapped to the component.
     */
    abstract protected function consumeJsonContent($jsonContent);

    /**
     * @param [type] $viewData The entity / collection to be passed to the form component.
     * @return string|null The serialised JSON of this component.
     */
    abstract protected function provideJsonContent($viewData): ?string;
}
