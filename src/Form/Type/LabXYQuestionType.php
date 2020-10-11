<?php

/*
LabXYQuestionType.php
Gareth Sears - 2493194S
*/

namespace App\Form\Type;

use App\Entity\LabXYQuestion;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Generates a form for setting danger zones using an XY component which is bound to a
 * LabXYQuestion entity.
 */
class LabXYQuestionType extends AbstractType
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Create a default form element type for danger zones.
        $builder->add('dangerZones');

        // Wait until the labXYQuestion has been 'hydrated' with database data, so that we can
        // query it for the question information.
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
            $labXYQuestion = $event->getData();

            // Get the XYQuestion information
            $xyQuestion = $labXYQuestion->getXYQuestion();
            $xField = $xyQuestion->getXField();
            $yField = $xyQuestion->getYField();

            $form = $event->getForm();

            // Serialize the coordinates of all XY responses.
            $coordinatesArray = [];

            foreach ($labXYQuestion->getResponses()->toArray() as $response) {
                $coordinates = $response->getCoordinates();
                if ($coordinates) {
                    $coordinatesArray[] = $coordinates;
                }
            }

            $jsonCoordinates = $this->serializer->serialize($coordinatesArray, 'json');

            $form
                // Map the dangerZones attribute of the LabXYQuestion to the (sub)form
                // LabXYquestionDangerZoneType.
                ->add('dangerZones', LabXYQuestionDangerZoneType::class, [
                    'label' => $xyQuestion->getName(),
                    'x_label_low' => $xField->getLowLabel(),
                    'x_label_high' => $xField->getHighLabel(),
                    'y_label_low' => $yField->getLowLabel(),
                    'y_label_high' => $yField->getHighLabel(),
                    // Can be blank (no danger zones)
                    'not_blank' => false,
                    'cell_size' => 0.9,
                    // Pass the coordinates of all responses to this labXYQuestion
                    // so they are visible.
                    'coordinates' => $jsonCoordinates,
                    'read_only' => $options['read_only']
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Bind this form type to a LabXYQuestion entity.
            'data_class' => LabXYQuestion::class,
            'read_only' => false,
        ]);
    }
}
