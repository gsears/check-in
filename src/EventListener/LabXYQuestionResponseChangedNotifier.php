<?php

namespace App\EventListener;

use App\Entity\LabXYQuestionResponse;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * https://symfony.com/doc/current/doctrine/events.html
 */
class LabXYQuestionResponseChangedNotifier
{

    public function postPersist(LabXYQuestionResponse $response, LifecycleEventArgs $event)
    {
        $this->updateRiskInLabResponse($response, $event);
    }

    public function postUpdate(LabXYQuestionResponse $response, LifecycleEventArgs $event)
    {
        $this->updateRiskInLabResponse($response, $event);
    }

    private function updateRiskInLabResponse(LabXYQuestionResponse $response, LifecycleEventArgs $event)
    {

        /** @var LabXYQuestionResponse[] $responses */
        $responses = $dangerZone
            ->getLabXYQuestion()
            ->getResponses()
            ->toArray();

        foreach ($responses as $response) {

            $xVal = $response->getXValue();
            $yVal = $response->getYValue();

            if (
                $xVal >= $dangerZone->getXMin() &&
                $xVal <= $dangerZone->getXMax() &&
                $yVal >= $dangerZone->getYMin() &&
                $yVal <= $dangerZone->getYMax()
            ) {
                $response->setRiskLevel($dangerZone->getRiskLevel());
            }
        }

        $event->getObjectManager()->flush();
    }
}
