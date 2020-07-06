<?php
namespace Traffic\Pipeline\Stage;

use Component\Landings\Repository\LandingRepository;
use Traffic\Actions\LandingOfferRotator;
use Traffic\Logging\TrafficLogEntry;
use Traffic\Model\BaseStream;
use Traffic\Model\Landing;
use Traffic\Pipeline\Payload;
use Traffic\Repository\CachedLandingRepository;
use Traffic\Repository\CachedStreamLandingAssociationRepository;
use Traffic\Repository\CachedStreamOfferAssociationRepository;

class ChooseLandingStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $stream = $payload->getStream();
        $campaign = $payload->getCampaign();
        $rawClick = $payload->getRawClick();
        $currentLanding = $payload->getLanding();
        $serverRequest = $payload->getServerRequest();
        $response = $payload->getResponse();

        if (empty($response)) {
            throw new StageException('response is not set');
        }

        if (empty($serverRequest)) {
            throw new StageException("serverRequest is not defined");
        }

        if (empty($stream)) {
            $logEntry->add('No stream, skip choosing landing');
            return $payload;
        }

        if ($stream->getSchema() != BaseStream::LANDINGS &&
            $stream->getSchema() != BaseStream::OFFERS) {
            $logEntry->add('Schema is ' . $stream->getSchema() .', skip choosing landings');
            return $payload;
        }
        if (empty($campaign)) {
            throw new StageException('campaign is not defined');
        }

        if (empty($rawClick)) {
            throw new StageException('rawClick is not defined');
        }

        if (!empty($currentLanding)) {
            $logEntry->add('Landing is preselected #' . $currentLanding->getId());
            return $payload;
        }

        if ($rawClick->getLandingUrl()) {
            $landing = $this->_findLandingFromUrl($rawClick->getLandingUrl());
        }

        if (empty($landing)) {
            $rotator = LandingOfferRotator::factory([
                'cookieName' => 'lp' . $campaign->getId(),
                'repository' => CachedLandingRepository::instance(),
                'cookieTtl' => $campaign->getCookiesTtl(),
                'bindEnabled' => $campaign->isBindVisitorsLandingEnabled(),
                'associationField' => 'landing_id'
            ]);
            $landingAssociations = CachedStreamLandingAssociationRepository::instance()->getCachedByStream($stream);
            if (empty($landingAssociations)) {
                $logEntry->add('No landings');
            } else {
                $landing = $rotator->getRandom($serverRequest, $landingAssociations, $logEntry);

                if ($campaign->isBindVisitorsLandingEnabled() && !empty($landing)) {
                    list($serverRequest, $response) = $rotator->bindToEntity($serverRequest, $response, $landing);
                    $payload->setServerRequest($serverRequest);
                    $payload->setResponse($response);
                }
            }
        }

        if (!empty($landing)) {
            $logEntry->add('LP #' . $landing->getId() .' is chosen');
            $this->_updatePayload($payload, $landing);
        } else {
            $logEntry->add('No LP selected');
        }

        return $payload;
    }

    private function _findLandingFromUrl($landingUrl)
    {
        $domain = preg_replace('#\/.*$#', '', $landingUrl);
        $landings = CachedLandingRepository::instance()->urls();

        if (empty($landingUrl)) {
            return null;
        }

        $possibleLandingId = null;
        foreach ($landings as $id => $url) {
            if (!empty($domain) && strpos($url, $domain) !== false) {
                $possibleLandingId = $id;
            }
            if (strpos($url, $landingUrl) !== false) {
                return CachedLandingRepository::instance()->findCached($id);
            }
        }

        if ($possibleLandingId) {
            return CachedLandingRepository::instance()->findCached($possibleLandingId);
        }
        return null;
    }

    private function _updatePayload(Payload $payload, Landing $landing)
    {
        $payload->setActionPayload($landing->getActionPayload());
        $payload->getRawClick()->setLandingId($landing->getId());
        $payload->setLanding($landing);
        $payload->setActionType($landing->getActionType());
        $payload->setActionOptions($landing->getActionOptions());

        if ($payload->getStream() && CachedStreamOfferAssociationRepository::instance()->hasCachedOffers($payload->getStream())) {
            $payload->setNeedToken(true);
            $payload->setAddTokenToUrl(true);
        }

    }
}