<?php

namespace App\Form\Type;

use App\Entity\XYQuestion;
use App\Form\Type\XYCoordinates;
use App\Entity\LabResponse;
use App\Form\Type\XYCoordinatesType;
use App\Entity\LabXYQuestion;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use App\Entity\LabXYQuestionResponse;
use App\Entity\LabXYQuestionDangerZone;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Constraints\NotBlank;

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

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $labXYQuestion = $event->getData();

            $xyQuestion = $labXYQuestion->getXYQuestion();
            $xField = $xyQuestion->getXField();
            $yField = $xyQuestion->getYField();

            $form = $event->getForm();

            // Serialize the coordinates of the responses
            $coordinatesArray = [];

            foreach ($labXYQuestion->getResponses()->toArray() as $response) {
                $coordinates = $response->getCoordinates();
                if ($coordinates) {
                    $coordinatesArray[] = $coordinates;
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
                    'coordinates' => $jsonCoordinates
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Set that this form is bound to an SurveyQuestionResponseInterface entity
        $resolver->setDefaults([
            'data_class' => LabXYQuestion::class,
        ]);
    }
}