<?php
namespace Traffic\Command\DelayedCommand;

use Component\Clicks\ClickProcessing\ExtractReferences;
use Component\Clicks\ClickProcessing\FilterAttributes;
use Component\Clicks\ClickProcessing\LoadClicks;
use Component\Clicks\ClickProcessing\UpdateClicks;
use Core\Currency\Service\CurrencyService;
use Component\DelayedCommands\DelayedCommandInterface;
use Traffic\CommandQueue\Service\DelayedCommandService;
use Core\Model\AbstractModel;
use Traffic\Logging\Service\LoggerService;
use Traffic\Model\Click;
use Traffic\Profiler\ProfilerService;
use Traffic\Repository\CachedOfferRepository;
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Service\SettingsService;
use Traffic\Tools\Tools;

class UpdateClickCommand implements DelayedCommandInterface
{
    const NAME = 'update_click';

    protected $_pipeline;

    public function __construct()
    {
        $this->_pipeline = [
            new LoadClicks(),
            new ExtractReferences(true),
            new FilterAttributes(false),
            new UpdateClicks()
        ];
    }

    public function priority()
    {
        return 2;
    }

    public function process($entries)
    {
        $count = count($entries);
        foreach ($this->_pipeline as $stage) {
            $stageName = Tools::demodulize(get_class($stage));
            LoggerService::instance()->debug(ProfilerService::instance()->step("{$stageName} start ({$count} items)"));
            $entries = $stage->process($entries);
            LoggerService::instance()->debug(ProfilerService::instance()->step("{$stageName} end"));
        }

        unset($entries);
    }

    public static function saveLpClick($subId, $offerId, $params = null, $landingId = null)
    {
        $payload = [];
        $payload['sub_id'] = $subId;
        $payload['offer_id'] = $offerId;

        $offer = CachedOfferRepository::instance()->findCached($offerId);

        if (empty($offer)) {
            LoggerService::instance()->info('UpdateLpClick: Offer #' . $offerId . ' is not found. Request sent from landing: ' .$landingId. '. Skip updating.');
            return;
        }
        $payload['affiliate_network_id'] = $offer->getAffiliateNetworkId();

        if ($landingId) {
            $datetime = (new \Datetime())->format(AbstractModel::DATETIME_FORMAT);
            $payload['landing_clicked'] = 1;
            $payload['landing_clicked_datetime'] = $datetime;
        }

        if ($offer->isCPC() && !$offer->isPayoutAuto()) {
            $payload['is_sale'] = 1;
            $revenue = CurrencyService::instance()->exchange(
                $offer->getPayoutValue(),
                $offer->getPayoutCurrency(),
                CachedSettingsRepository::instance()->get('currency')
            );

            $payload['revenue'] = $revenue;
        }

        for ($i = 1; $i <= Click::getSubIdCount(); $i++) {
            $subIdParam = 'sub_id_' . $i;
            if (!empty($params[$subIdParam])) {
                $payload[$subIdParam] = urldecode($params[$subIdParam]);
            }
        }

        for ($i = 1; $i <= Click::EXTRA_PARAM_COUNT; $i++) {
            if (!empty($params['extra_param_' . $i])) {
                $payload['extra_param_' . $i] = urldecode($params['extra_param_' . $i]);
            }
        }

        if (!empty($params['is_bot'])) {
            $payload['is_bot'] = (int)$params['is_bot'];
        }
        DelayedCommandService::instance()->initRetry($payload, self::NAME);

        $command = [
            DelayedCommandService::PAYLOAD => $payload,
            DelayedCommandService::COMMAND => self::NAME
        ];
        DelayedCommandService::instance()->push($command);
        return $command;
    }

    public static function updateTokens($subId, $params)
    {
        $payload = [];
        $payload['sub_id'] = $subId;

        for ($i = 1; $i <= Click::getSubIdCount(); $i++) {
            $subIdParam = 'sub_id_' . $i;
            if (!empty($params[$subIdParam])) {
                $payload[$subIdParam] = urldecode($params[$subIdParam]);
            }
        }

        for ($i = 1; $i <= Click::EXTRA_PARAM_COUNT; $i++) {
            if (!empty($params['extra_param_' . $i])) {
                $payload['extra_param_' . $i] = urldecode($params['extra_param_' . $i]);
            }
        }

        if (!empty($params['offer_id'])) {
            $payload['offer_id'] = $params['offer_id'];
            $offer = CachedOfferRepository::instance()->findCached($payload['offer_id']);
            $payload['affiliate_network_id'] = $offer->getAffiliateNetworkId();
        }

        if (!empty($params['is_bot'])) {
            $payload['is_bot'] = (int)$params['is_bot'];
        }
        DelayedCommandService::instance()->initRetry($payload, self::NAME);

        $command = [
            DelayedCommandService::PAYLOAD => $payload,
            DelayedCommandService::COMMAND => self::NAME
        ];
        DelayedCommandService::instance()->push($command);
        return $command;
    }
}