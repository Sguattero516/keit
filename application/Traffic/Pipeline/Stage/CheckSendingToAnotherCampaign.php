<?php
namespace Traffic\Pipeline\Stage;

use Traffic\Pipeline\Payload;
use Traffic\Logging\TrafficLogEntry;

class CheckSendingToAnotherCampaign implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        if ($payload->getActionType() === 'campaign' || $payload->getActionType() === 'group') {
            $payload->setForcedCampaignId($payload->getActionPayload());
            $payload->abort();
        }

        return $payload;
    }
}