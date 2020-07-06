<?php
namespace Traffic\Pipeline\Stage;

use Traffic\Session\Service\UniquenessSessionService;
use Traffic\Pipeline\Payload;
use Traffic\Logging\TrafficLogEntry;

class UpdateCampaignUniquenessSessionStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $campaign = $payload->getCampaign();
        $rawClick = $payload->getRawClick();
        $serverRequest = $payload->getServerRequest();

        if (empty($campaign)) {
            throw new StageException('Empty campaign');
        }

        if (empty($rawClick)) {
            throw new StageException('Empty rawClick');
        }

        // make bots always non-unique KTDS-1063
        if ($rawClick->isBot()) {
            $rawClick->set('is_unique_campaign', false);
            return $payload;
        }
        $rawClick->set('is_unique_campaign', UniquenessSessionService::instance()->isUniqueForCampaign($serverRequest, $rawClick, $campaign));
        $rawClick->set('is_unique_global', UniquenessSessionService::instance()->isUniqueGlobal($serverRequest, $rawClick, $campaign));

        $logEntry->addLazy(function() use ($serverRequest) {
            json_encode($serverRequest->getCookieParams());
        });

        if (!$rawClick->isUniqueCampaign()) {
            $logEntry->add("Is not unique for campaign");
        }

        return $payload;
    }
}