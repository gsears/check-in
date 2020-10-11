<?php

/*
SentimentAnalysisTextType.php
Gareth Sears - 2493194S
*/

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Generates a customised TextareaType form with a different view template
 * which includes the nudge UI to persuade users to submit fuller sentiment
 * analysis responses.
 */
class SentimentAnalysisTextType extends AbstractType
{
    public function getBlockPrefix()
    {
        // Defines the 'block' in custom_types.html.twig which is used to
        // render this form component.
        return 'sentiment_text';
    }

    public function getParent()
    {
        return TextareaType::class;
    }


    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Pass the desired character count into the view template context.
        $view->vars['targetCharacterCount'] = $options['targetCharacterCount'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Allow the user to change the desired character count. Set default to 150.
        $resolver->setDefault('targetCharacterCount', 150);
    }
}
