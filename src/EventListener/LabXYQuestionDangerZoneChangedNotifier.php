<?php

namespace App\EventListener;

use App\Entity\LabXYQuestionDangerZone;
use App\Entity\LabXYQuestionResponse;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * https://symfony.com/doc/current/doctrine/events.html
 */
class LabXYQuestionDangerZoneChangedNotifier
{

    public function postPersist(LabXYQuestionDangerZone $dangerZone, LifecycleEventArgs $event)
    {
        $this->updateRiskInResponses($dangerZone, $event);
    }

    public function postUpdate(LabXYQuestionDangerZone $dangerZone, LifecycleEventArgs $event)
    {
        $this->updateRiskInResponses($dangerZone, $event);
    }

    private function updateRiskInResponses(LabXYQuestionDangerZone $dangerZone, LifecycleEventArgs $event)
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
