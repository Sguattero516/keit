<?php
namespace Traffic\Pipeline\Stage;

use Exception;
use Traffic\Logging\TrafficLogEntry;
use Traffic\Model\Campaign;
use Traffic\Model\Setting;
use Traffic\Pipeline\Payload;
use Traffic\Repository\CachedCampaignRepository;
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Response\ResponseFactory;
use Traffic\Response\StatusCode;

class CheckDefaultCampaignStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        if ($payload->getCampaign()) {
            return $payload;
        }

        $logEntry->add('Check default action from settings');

        $request = $payload->getServerRequest();

        if (empty($request)) {
            throw new StageException('Empty request');
        }

        $result = null;
        switch (CachedSettingsRepository::instance()->get(Setting::EXTRA_ACTION)) {
            case Setting::EXTRA_ACTION_PARAM_CAMPAIGN:
                $payload = $this->_triggerCampaign($payload, $logEntry);
                break;
            case Setting::EXTRA_ACTION_PARAM_REDIRECT:
                $payload = $this->_triggerRedirect($payload, $logEntry);
                break;
            default:
                $payload = $this->_triggerNotFound($payload, $logEntry);
        }

        return $payload;
    }

    /**
     * @param Payload $payload
     * @param TrafficLogEntry $logger
     * @return Payload
     * @throws Exception
     */
    private function _triggerRedirect(Payload $payload, TrafficLogEntry $logger)
    {
        $extraUrl = CachedSettingsRepository::instance()->get('extra_url');
        $logger->add('Redirecting to ' . $extraUrl);
        $response = $payload->getResponse()
            ->withStatus(StatusCode::MOVED_TEMPORARILY)
            ->withHeader('Location', $extraUrl);
        $payload->setResponse($response);
        $payload->abort();
        return $payload;
    }

    /**
     * @param Payload $payload
     * @param TrafficLogEntry $logger
     * @return Payload
     * @throws Exception
     */
    private function _triggerNotFound(Payload $payload, TrafficLogEntry $logger)
    {
        $response = $payload->getResponse()
            ->withStatus(StatusCode::NOT_FOUND)
            ->withBody(ResponseFactory::safeBody('Default campaign not found'));
        $logger->add('Shows 404 NotFound');
        $payload->setResponse($response);
        $payload->abort();
        return $payload;
    }

    /**
     * @param Payload $payload
     * @param TrafficLogEntry $logger
     * @return Payload
     * @throws StageException
     */
    private function _triggerCampaign(Payload $payload, TrafficLogEntry $logger)
    {
        $id = CachedSettingsRepository::instance()->get(Setting::EXTRA_CAMPAIGN);
        $logger->add('Sending to default campaign #' . $id . '');
        /** @var Campaign $campaign */
        $campaign = CachedCampaignRepository::instance()->findInCacheById($id);
        if (empty($campaign)) {
            throw new StageException('Default campaign missing. Check default action settings');
        }
        if (!$campaign->isActive()) {
            $logger->add('Default campaign #' . $id . ' is not active, redirecting to 404');
            return $this->_triggerNotFound($payload, $logger);
        }
        $payload->setForcedCampaignId($id);
        $payload->abort();
        return $payload;
    }
}
