<?php

namespace App\Form\Type;

use App\Entity\XYQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class XYQuestionType extends AbstractType
{
    private $xyQuestion;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->xyQuestion = $builder->getData();
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['name'] = $this->xyQuestion->getName();
        $view->vars['questionText'] = $this->xyQuestion->getQuestionText();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set that this form is bound to an XYQuestion entity
        $resolver->setDefaults([
            'data_class' => XYQuestion::class,
        ]);
    }
}
