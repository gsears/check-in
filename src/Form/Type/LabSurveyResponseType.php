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

class LabSurveyResponseType extends AbstractType
{
    private $surveyResponse;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->surveyResponse = $builder->getData();

        $questions = $this->surveyResponse
            ->getLabSurvey()
            ->getQuestions()
            ->map(function($labSurveyQuestion) {
                // Get the original question instance
                return $labSurveyQuestion->getQuestion();
            })
            ->toArray();

        foreach ($questions as $question) {
            if($question instanceof XYQuestion) {

                $xField = $question->getXField();
                $yField = $question->getYField();

                dump($question);

                // Add to the form
                $builder->add('id', XYQuestionType::class, [
                    'label' => $question->getName(),
                    'question_text' => $question->getQuestionText(),
                    'help' => 'Click to set the x-y value',
                    'x_label_low' => $xField->getLowLabel(),
                    'x_label_high' => $xField->getHighLabel(),
                    'y_label_low' => $yField->getLowLabel(),
                    'y_label_high' => $yField->getHighLabel(),
                ]);
            }
        }
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
