<?php
namespace Traffic\Pipeline\Stage;

use Traffic\LpToken\Service\LpTokenService;
use Traffic\Cookies\Service\CookiesService;
use Traffic\Repository\CachedStreamOfferAssociationRepository;
use Traffic\Service\UrlService;
use Traffic\Actions\Repository\StreamActionRepository;
use Traffic\Pipeline\Payload;
use Traffic\Logging\TrafficLogEntry;

class GenerateTokenStage implements StageInterface
{
    const HEADER_LIMIT_FOR_COOKIES = 3060;  //bytes
    const TOKEN_PARAM = '_token';
    const SUBID_PARAM = '_subid';

    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $serverRequest = $payload->getServerRequest();
        $response = $payload->getResponse();
        $rawClick = $payload->getRawClick();
        $stream = $payload->getStream();

        if (!$payload->isTokenNeeded()) {
            $logEntry->add('Token is not needed. ');
            return $payload;
        }

        if ($payload->getRawClick()->getToken()) {
            return $payload;
        }

        if (empty($rawClick)) {
            throw new StageException('GenerateTokenStage: Empty rawClick');
        }

        if (empty($serverRequest)) {
            throw new StageException('serverRequest is not set');
        }

        if (empty($response)) {
            throw new StageException('response is not set');
        }

        if (empty($stream)) {
            return $payload;
        }

        $offers = CachedStreamOfferAssociationRepository::instance()->getCachedByStream($stream);

        if (empty($offers)) {
            $logEntry->add('Token is not needed because the stream does not contain any offers');
            return $payload;
        }

        $token = LpTokenService::instance()->storeRawClick($rawClick);
        if (strlen($token) < self::HEADER_LIMIT_FOR_COOKIES) {
            list($serverRequest, $response) = CookiesService::instance()->setRaw(
                $serverRequest,
                $response,
                self::TOKEN_PARAM,
                $token);
            $payload->setServerRequest($serverRequest);
            $payload->setResponse($response);
        }
        if (
            $payload->shouldAddTokenToURL() &&
            StreamActionRepository::instance()->isRedirect($payload->getActionType())
        ) {
            $newActionPayload = $payload->getActionPayload();
            $subidParam =  self::SUBID_PARAM . '=' . $payload->getRawClick()->getSubId();
            $tokenParam = self::TOKEN_PARAM . '=' . $token;
            $newActionPayload = UrlService::instance()->addParameterToUrl($newActionPayload, $subidParam);
            $newActionPayload = UrlService::instance()->addParameterToUrl($newActionPayload, $tokenParam);
            $payload->setActionPayload($newActionPayload);
        }
        $payload->getRawClick()->setToken($token);

        return $payload;
    }
}