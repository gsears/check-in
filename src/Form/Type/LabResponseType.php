<?php

namespace App\Form\Type;

use App\Entity\Lab;
use App\Entity\LabResponse;
use App\Entity\SurveyQuestionResponseInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

class LabResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Uses the collection type field to render a form element for each xyQuestion
        // from the
        $builder->add('labXYQuestions', CollectionType::class, [
            'mapped' => false,
            'data' => $builder->getData()->getLab()->getLabXYQuestions(),
            'label' => 'XY Questions',
            'entry_type' => LabXYQuestionType::class,
            'entry_options' => [
                'label' => false,
                'read_only' => $options['read_only'],
                'filter_by_student' => $builder->getData()->getStudent(),
            ],
            'attr' => [
                'class' => 'flex-form-collection',
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set that this form is bound to an SurveyQuestionResponseInterface entity
        $resolver->setDefaults([
            'data_class' => LabResponse::class,
            'read_only' => true,
        ]);
    }
}
