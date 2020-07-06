<?php
namespace Traffic\Pipeline\Stage;

use Traffic\Session\Service\UniquenessSessionService;
use Traffic\Pipeline\Payload;
use Traffic\Logging\TrafficLogEntry;

class UpdateStreamUniquenessSessionStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $campaign = $payload->getCampaign();
        $stream = $payload->getStream();
        $rawClick = $payload->getRawClick();
        $serverRequest = $payload->getServerRequest();

        if (empty($campaign)) {
            throw new StageException('Empty campaign');
        }

        if (empty($rawClick)) {
            throw new StageException('Empty rawClick');
        }

        if (empty($stream)) {
            return $payload;
        }

        // make bots always non-unique KTDS-1063
        if ($rawClick->isBot()) {
            $rawClick->set('is_unique_stream', false);
            return $payload;
        }

        $rawClick->set('is_unique_stream', UniquenessSessionService::instance()->isUniqueForStream($serverRequest, $rawClick, $campaign, $stream));

        if (!$rawClick->isUniqueStream()) {
            $logEntry->add("Is not unique for stream");
        }

        return $payload;
    }
}