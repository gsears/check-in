<?php

/*
LabSentimentQuestionResponseType.php
Gareth Sears - 2493194S
*/

namespace App\Form\Type;

use App\Entity\LabSentimentQuestionResponse;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Generates a form which can be bound to a LabSentimentQuestionResponse entity.
 */
class LabSentimentQuestionResponseType extends SurveyQuestionResponseType
{
    public function buildFormBody(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        // Get the sentiment question associated with the LabSentimentQuestionResponse.
        $sentimentQuestionResponse = $builder->getData();
        $sentimentQuestion = $sentimentQuestionResponse
            ->getLabSentimentQuestion()
            ->getSentimentQuestion();

        // Associate the text entity property with a SentimentAnalysisTextType form.
        // This ensures it has a nudge interface for encouraging responses.
        $builder
            ->add('text', SentimentAnalysisTextType::class, [
                'label' => $sentimentQuestion->getQuestionText(),
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please complete the text field or skip.'
                    ])
                ],
            ]);

        return $builder;
    }

    public function getOptionDefaults(): array
    {
        return [
            // Bind this form type to a LabSentimentQuestionResponse entity.
            'data_class' => LabSentimentQuestionResponse::class,
        ];
    }
}
