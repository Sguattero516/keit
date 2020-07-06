<?php
namespace Traffic\Pipeline\Stage;

use Traffic\Pipeline\Payload;
use Traffic\Service\RawClickService;
use Traffic\Service\VisitorService;
use Traffic\Logging\TrafficLogEntry;
use Traffic\Cookies\Service\CookiesService;

class UpdateRawClickStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $rawClick = $payload->getRawClick();
        $campaign = $payload->getCampaign();
        $serverRequest = $payload->getServerRequest();
        $response = $payload->getResponse();

        if (empty($rawClick)) {
            throw new StageException('rawClick is not set');
        }

        if (empty($campaign)) {
            throw new StageException('campaign is not set');
        }

        if (empty($response)) {
            throw new StageException('response is not set');
        }

        $rawClick->setCampaignId($campaign->getId());

        $trafficSourceId = $campaign->get('traffic_source_id');
        $rawClick->set('ts_id', $trafficSourceId);

        $rawClick->setVisitorCode(VisitorService::instance()->generateCode($rawClick));
        $rawClick->setSubId(RawClickService::instance()->generate($rawClick->getVisitorCode()));

        list($serverRequest, $response) = CookiesService::instance()->setRaw(
            $serverRequest,
            $response,
            CookiesService::SUB_ID_KEY,
            $rawClick->getSubId()
        );
        $payload->setServerRequest($serverRequest);
        $payload->setResponse($response);
        $payload->setRawClick($rawClick);

        return $payload;
    }
}
