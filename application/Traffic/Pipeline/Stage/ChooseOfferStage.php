<?php
namespace Traffic\Pipeline\Stage;

use Component\Conversions\ConversionCapacity\Service\ConversionCapacityService;
use Traffic\Model\BaseStream;
use Traffic\Repository\CachedOfferRepository;
use Traffic\Actions\LandingOfferRotator;
use Traffic\Pipeline\Payload;
use Traffic\Logging\TrafficLogEntry;
use Traffic\Repository\CachedStreamOfferAssociationRepository;

class ChooseOfferStage implements StageInterface
{
    // from which version we started send only token
    const VERSION_SEND_TOKEN_ONLY = 2;

    const IGNORE_OFFER_PARAM = 'exit';

    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $stream = $payload->getStream();
        $campaign = $payload->getCampaign();
        $rawClick = $payload->getRawClick();
        $landing = $payload->getLanding();
        $serverRequest = $payload->getServerRequest();
        $response = $payload->getResponse();

        if (empty($response)) {
            throw new StageException('response is not set');
        }

        if (empty($serverRequest)) {
            throw new StageException("serverRequest is empty");
        }

        if (empty($stream)) {
            $logEntry->add('No stream, skip choosing offer');
            return $payload;
        }

        if (empty($campaign)) {
            throw new StageException('Empty campaign');
        }

        if (empty($rawClick)) {
            throw new StageException('Empty rawClick');
        }

        if (!empty($landing) && !$payload->isForceChooseOffer()) {
            $logEntry->add('Landing is chosen, skip choosing offer');
            return $payload;
        }

        if ($stream->getSchema() != BaseStream::LANDINGS &&
            $stream->getSchema() != BaseStream::OFFERS) {
            $logEntry->add('Schema is \'' .$stream->getSchema(). '\' so offer is not needed.');
            return $payload;
        }


        // Trying forced offer
        if ($payload->getForcedOfferId() &&
            CachedStreamOfferAssociationRepository::instance()->cachedStreamHasOfferId($stream, $payload->getForcedOfferId())) {

            $logEntry->add('Loading offer #' . $payload->getForcedOfferId());
            $offer =  CachedOfferRepository::instance()->findCached($payload->getForcedOfferId());
        }

        if (empty($offer)) {
            $rotator = LandingOfferRotator::factory([
                'cookieName' => 'of' . $campaign->getId(),
                'repository' => CachedOfferRepository::instance(),
                'cookieTtl' => $campaign->getCookiesTtl(),
                'bindEnabled' => $campaign->isBindVisitorsOfferEnabled(),
                'associationField' => 'offer_id'
            ]);

            $offerAssociations = CachedStreamOfferAssociationRepository::instance()->getCachedByStream($stream, false);
            if (empty($offerAssociations)) {
                $logEntry->add('No offers in the stream');
            } else {
                $offer = $rotator->getRandom($serverRequest, $offerAssociations, $logEntry);
                if (empty($offer)) {
                    $logEntry->add('Rotator return empty result');
                }
                if ($campaign->isBindVisitorsOfferEnabled() && !empty($offer)) {
                    list($serverRequest, $response) = $rotator->bindToEntity($serverRequest, $response, $offer);
                    $payload->setServerRequest($serverRequest);
                    $payload->setResponse($response);
                }
            }
        }

        if (empty($offer)) {
            $logEntry->add('No offer is chosen');
            return $payload;
        }

        $newOffer = ConversionCapacityService::instance()->findAvailableOffer($offer);

        if ($newOffer->getId() != $offer->getId()) {
            $logEntry->add('Offer #' . $offer->getId() . ' reach his conversion capacity. An alternative is chosen #' . $newOffer->getId());
            $offer = $newOffer;
        }

        if (empty($offer)) {
            $logEntry->add('Warning! Offer #' . $offer->getId() . ' doesn\'t have alternative offer in his chain.');
            return $payload;
        }

        $logEntry->add('Offer #' . $offer->getId() .' is chosen');

        if ($payload->getServerRequest()->getParam(self::IGNORE_OFFER_PARAM) != 1) {
            $payload->getRawClick()->setOfferId($offer->getId());
        }

        $payload->setOffer($offer);
        $payload->setNeedToken(true);

        if ($payload->isForceRedirectOffer()) {
            $payload->setActionType($offer->getActionType());
            $payload->setActionPayload($offer->getActionPayload());
            $payload->setActionOptions($offer->getActionOptions());
        }

        return $payload;
    }

}