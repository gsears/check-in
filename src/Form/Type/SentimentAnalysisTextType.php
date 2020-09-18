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

class SentimentAnalysisTextType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'sentiment_text';
    }

    public function getParent()
    {
        return TextareaType::class;
    }


    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['targetCharacterCount'] = $options['targetCharacterCount'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('targetCharacterCount', 150);
    }
}
