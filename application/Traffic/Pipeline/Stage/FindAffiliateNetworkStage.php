<?php
namespace Traffic\Pipeline\Stage;

use Component\Offers\Service\OfferService;
use Traffic\Pipeline\Payload;
use Traffic\Logging\TrafficLogEntry;
use Traffic\Repository\CachedAffiliateNetworkRepository;

class FindAffiliateNetworkStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $offer = $payload->getOffer();

        if (empty($offer)) {
            return $payload;
        }

        if ($offer->getAffiliateNetworkId()) {
            $network = CachedAffiliateNetworkRepository::instance()->findCached($offer->getAffiliateNetworkId());
        }

        if (!empty($network)) {
            $actionPayload = OfferService::instance()->addParameterToUrl($payload->getActionPayload(), $network->get('offer_param'));
            $payload->getRawClick()->setAffiliateNetworkId($network->getId());
            $payload->setActionPayload($actionPayload);
        }
        return $payload;
    }
}