<?php

namespace Traffic\Cookies\Service;

use BjoernGoetschke\Psr7Cookies\HttpResponseCookies;
use Exception;
use Firebase\JWT\JWT;
use Traffic\Request\ServerRequest;
use Traffic\Response\Response;
use Traffic\Service\AbstractService;
use Traffic\Logging\Service\LoggerService;

class CookiesService extends AbstractService
{
    const SUB_ID_KEY = '_subid';

    /**
     * @param ServerRequest $serverRequest
     * @param Response $response
     * @param $cookieName
     * @param $data
     * @param null $expire
     * @return ServerRequest[]|Response[]
     */
    public function encodeAndSet(ServerRequest $serverRequest, Response $response, $cookieName, $data, $expire = null)
    {
        $json = $this->_encode($data);
        return $this->setRaw($serverRequest, $response, $cookieName, $json, $expire);
    }

    /**
     * @param ServerRequest $serverRequest
     * @param Response $response
     * @param string $cookieName
     * @param string|int $rawData
     * @param null|int $expire
     * @param boolean $httpOnly
     * @param string $path
     * @param string $domain
     * @return ServerRequest[]|Response[]
     */
    public function setRaw(
        ServerRequest $serverRequest,
        Response $response,
        $cookieName,
        $rawData,
        $expire = null,
        $httpOnly = false,
        $path = '/',
        $domain = null
    ) {
        $cookieParams = $serverRequest->getCookieParams();
        $cookieParams[$cookieName] = $rawData;
        $serverRequest = $serverRequest->withCookieParams($cookieParams);

        if (empty($expire)) {
            $expire = $this->_getDefaultMaxAge();
        }

        $cookies = new HttpResponseCookies($response);
        $cookies->setCookieFor($cookieName, $rawData, $expire, $path, $domain, false, $httpOnly);
        $response = $cookies->getResponse();

        return [$serverRequest, $response];
    }

    public function unsetCookie(ServerRequest $serverRequest, Response $response, $cookieName)
    {
        $cookies = new HttpResponseCookies($response);
        $cookies->unsetCookie($cookieName);
        $response = $cookies->getResponse();
        return [$serverRequest, $response];
    }

    public function decodeAndGet(ServerRequest $serverRequest, $cookieName)
    {
        if ($serverRequest->getCookieParam($cookieName)) {
            return $this->_decode($serverRequest->getCookieParam($cookieName));
        }
        return null;
    }

    public function getAll(ServerRequest $serverRequest)
    {
        return $serverRequest->getCookieParams();
    }

    public function getRaw(ServerRequest $serverRequest, $cookieName)
    {
        return $serverRequest->getCookieParam($cookieName);
    }

    private function _encode($data)
    {
        try {
            return JWT::encode(['data' => json_encode($data)], SALT, 'HS256');
        } catch (Exception $e) {
            LoggerService::instance()->debug("Sentinel error: " . $e->getMessage());
        }
        return null;
    }

    private function _decode($rawData)
    {
        try {
            $token = JWT::decode($rawData, SALT, ['HS256']);
            if (!empty($token->data)) {
                return json_decode($token->data, true);
            }
        } catch (Exception $e) {
            LoggerService::instance()->debug("JWT error: " . $e->getMessage() . ' (' . (string)$rawData . ')');
        }
        return null;
    }

    private function _getDefaultMaxAge()
    {
        return 60 * 60 * 24 * 31;
    }
}
