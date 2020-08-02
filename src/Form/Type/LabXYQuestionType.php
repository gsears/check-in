<?php

/*
LabXYQuestionType.php
Gareth Sears - 2493194S
*/

namespace App\Form\Type;

use App\Entity\LabXYQuestion;
use App\Entity\LabXYQuestionResponse;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class LabXYQuestionType extends AbstractType
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('dangerZones');

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
            $labXYQuestion = $event->getData();

            $xyQuestion = $labXYQuestion->getXYQuestion();
            $xField = $xyQuestion->getXField();
            $yField = $xyQuestion->getYField();

            $form = $event->getForm();

            // Serialize the coordinates of the responses
            $coordinatesArray = [];
            $student = $options['filter_by_student'];

            if ($student) {
                $studentResponse = $labXYQuestion
                    ->getResponses()
                    ->filter(function (LabXYQuestionResponse $response) use ($student) {
                        dump($student);
                        return $response->getLabResponse()->getStudent() === $student;
                    })->first();
                if ($studentResponse) {
                    $coordinatesArray[] = $studentResponse->getCoordinates();
                }
            } else {
                foreach ($labXYQuestion->getResponses()->toArray() as $response) {
                    $coordinates = $response->getCoordinates();
                    if ($coordinates) {
                        $coordinatesArray[] = $coordinates;
                    }
                }
            }

            $jsonCoordinates = $this->serializer->serialize($coordinatesArray, 'json');

            $form
                // Do not map the xy form component to the entity.
                ->add('dangerZones', LabXYQuestionDangerZoneType::class, [
                    'label' => $xyQuestion->getName(),
                    'x_label_low' => $xField->getLowLabel(),
                    'x_label_high' => $xField->getHighLabel(),
                    'y_label_low' => $yField->getLowLabel(),
                    'y_label_high' => $yField->getHighLabel(),
                    // Can be blank (no danger zones)
                    'not_blank' => false,
                    'cell_size' => 0.9,
                    // SET INITIAL DATA HERE
                    'coordinates' => $jsonCoordinates,
                    'read_only' => $options['read_only']
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set that this form is bound to an SurveyQuestionResponseInterface entity
        $resolver->setDefaults([
            'data_class' => LabXYQuestion::class,
            'filter_by_student' => null,
            'read_only' => false,
        ]);
    }
}
