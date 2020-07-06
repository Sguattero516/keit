<?php
namespace Traffic\Pipeline\Stage;

use Traffic\Session\Service\UniquenessSessionService;
use Traffic\Pipeline\Payload;
use Traffic\Logging\TrafficLogEntry;

class SaveUniquenessSessionStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $campaign = $payload->getCampaign();
        $stream = $payload->getStream();
        $rawClick = $payload->getRawClick();
        $request = $payload->getServerRequest();
        $response = $payload->getResponse();

        if (empty($request)) {
            throw new StageException('Error serverRequest is empty');
        }

        if (empty($response)) {
            throw new StageException('response is empty');
        }

        if (empty($campaign)) {
            throw new StageException('campaign is empty');
        }

        if (empty($rawClick)) {
            throw new StageException('rawClick is empty');
        }

        if ($stream) {
            list($request, $response) = UniquenessSessionService::instance()->save($request, $response, $rawClick, $campaign, $stream);
        } else {
            list($request, $response) = UniquenessSessionService::instance()->save($request, $response, $rawClick, $campaign);
        }

        $payload->setServerRequest($request);
        $payload->setResponse($response);

        return $payload;
    }
}