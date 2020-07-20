<?php

namespace App\Form\Type;

use App\Entity\LabSurveyResponse;
use App\Entity\LabSurveyXYQuestion;
use App\Entity\LabSurveyXYQuestionResponse;
use App\Entity\XYQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LabSurveyResponseType extends AbstractType
{
    private $surveyResponse;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->surveyResponse = $builder->getData();

        dump($this->surveyResponse);

        // Create an embedded form to map against xyQuestionResponses
        $builder->add('xyQuestionResponses', CollectionType::class, [
            'entry_type' => LabSurveyXYQuestionResponseType::class,
            'entry_options' => [
            ],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['lab'] = $this->surveyResponse->getLabSurvey();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set that this form is bound to an LabSurveyResponse entity
        $resolver->setDefaults([
            'data_class' => LabSurveyResponse::class,
        ]);


    }
}
