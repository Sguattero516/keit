<?php
namespace Traffic\Pipeline\Stage;

use Traffic\Repository\CachedCampaignRepository;
use Component\Domains\Repository\DomainsRepository;
use Traffic\Repository\CachedDomainRepository;
use Traffic\Repository\ParameterRepository;
use Traffic\Service\ConfigService;
use Traffic\Request\ServerRequest;
use Traffic\Pipeline\Payload;
use Traffic\Logging\TrafficLogEntry;

class FindCampaignStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $request = $payload->getServerRequest();

        if ($payload->getCampaign()) {
            return $payload;
        }

        if (empty($request)) {
            throw new StageException('Empty request');
        }

        if ($payload->getForcedCampaignId()) {
            $logEntry->add('[Restored] Processing campaign ' . $payload->getForcedCampaignId());
            $campaign = CachedCampaignRepository::instance()->findInCacheById($payload->getForcedCampaignId());
            $payload->setForcedCampaignId(null);
            $payload->setCampaign($campaign);
            return $payload;
        }

        $logEntry->add('Requested: ' . (string) $request->getUri());
        $logEntry->add('Searching campaign');

        $campaign = $this->_tryToFindCampaign($request);

        if (empty($campaign)) {
            $logEntry->add("Campaign is not found. Checking assigned to domain campaign.");
            $campaign = $this->_findDomainDefaultCampaign($request);
        }


        if (empty($campaign)) {
            return $payload;
        }

        if (!$campaign->isActive()) {
            $logEntry->add("Campaign is not active ({$campaign->getState()})");
            return $payload;
        }

        $payload->setCampaign($campaign);

        return $payload;
    }

    private function _tryToFindCampaign(ServerRequest $request)
    {
        $campaignAliases = $this->_getCampaignParamAliases($request);
        $campaign = null;
        foreach ($campaignAliases as $campaignAlias) {
            $campaign = CachedCampaignRepository::instance()->findByAlias($campaignAlias);
            if (empty($campaign) && ConfigService::instance()->get('system', 'allow_by_id')) {
                $campaign = CachedCampaignRepository::instance()->findInCacheById($campaignAlias);
            }
            if (!empty($campaign)) {
                return $campaign;
            }
        }
        return null;
    }

    private function _findDomainDefaultCampaign(ServerRequest $request)
    {
        if (empty($campaign)) {
            $campaignId = CachedDomainRepository::instance()->getCampaignIdByUrl($request->getUri());
            if (!empty($campaignId)) {
                return CachedCampaignRepository::instance()->findInCacheById($campaignId);
            }
        }
        return null;
    }

    private function _getCampaignParamAliases(ServerRequest $request)
    {
        $result = [];
        $param = $request->getParam('campaign');
        if (!empty($param)) {
            $result[] = $param;
        }

        $keys = ParameterRepository::instance()->getAliasesForId();

        foreach ($keys as $key) {
            $param = $request->getParam($key);
            if (!empty($param)) {
                $result[] = $param;
            }
        }

        $params = $request->getQueryParams();

        if (!empty($params)) {
            $keys = array_keys($params);
            $result = array_merge($result, $keys);
        }
        return $result;
    }
}