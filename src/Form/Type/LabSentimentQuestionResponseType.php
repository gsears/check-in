<?php

namespace App\Form\Type;

use App\Entity\LabSentimentQuestionResponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LabSentimentQuestionResponseType extends SurveyQuestionResponseType
{
    public function buildFormBody(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {

        $sentimentQuestionResponse = $builder->getData();
        $sentimentQuestion = $sentimentQuestionResponse
            ->getLabSentimentQuestion()
            ->getSentimentQuestion();

        $builder
            ->add('text', TextareaType::class, [
                'label' => $sentimentQuestion->getQuestionText(),
                'not_blank' => true
            ]);

        return $builder;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LabSentimentQuestionResponse::class,
        ]);
    }
}
