<?php
namespace Traffic\Pipeline\Stage;

use Core\Currency\Service\CurrencyService;
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Service\SettingsService;
use Traffic\Pipeline\Payload;
use Traffic\Logging\TrafficLogEntry;

class UpdatePayoutStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $offer = $payload->getOffer();
        $rawClick = $payload->getRawClick();

        if (empty($rawClick)) {
            throw new StageException('Empty rawClick');
        }

        if (!empty($offer) && $offer->isCPC() && !$offer->isPayoutAuto()) {
            $rawClick->set('is_sale', 1);
            $revenue = CurrencyService::instance()->exchange(
                $offer->getPayoutValue(),
                $offer->getPayoutCurrency(),
                CachedSettingsRepository::instance()->get('currency')
            );

            $rawClick->setRevenue($revenue);
            $payload->setRawClick($rawClick);
        }

        return $payload;
    }
}