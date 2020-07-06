<?php
namespace Traffic\Device\Service;

use Traffic\Request\ServerRequest;
use Traffic\Service\AbstractService;

class RealRemoteIpService extends AbstractService
{
    const LOCAL_MASK_1 = '192.168.';
    const LOCAL_MASK_2 = '127.0.';

    public function find(ServerRequest $request)
    {
        $possibleRealIps = [
            $this->_ipFromOperaMini($request),
            $this->_ipFromXRealIp($request),
            $this->_ipFromXForwardedFor($request),
            $this->_ipFromForwarded($request),
            $this->_ipFromCfConnectingIp($request),
            $this->_ipFromRemoteAddr($request),
            $this->_lastHope($request)
        ];

        foreach ($possibleRealIps as $ip) {
            if ($this->_isCorrectIpForHeader($ip)) {
                return $ip;
            }
        }

        return $ip;
    }

    /**
     * Opera mini имеет свои особенности https://dev.opera.com/articles/opera-mini-request-headers/
     * @param ServerRequest $serverRequest
     */
    private function _ipFromOperaMini(ServerRequest $serverRequest)
    {
        if (stristr($serverRequest->getHeaderLine(ServerRequest::HEADER_USER_AGENT), 'mini')) {
            $tmp = explode(',', $serverRequest->getHeaderLine(ServerRequest::HEADER_X_FORWARDED_FOR));
            return trim($tmp[count($tmp) - 2]);
        }
        return null;
    }

    /**
     * Берем из x-real-ip
     * @param ServerRequest $serverRequest
     * @return string
     */
    private function _ipFromXRealIp(ServerRequest $serverRequest)
    {
        return $serverRequest->getHeaderLine(ServerRequest::HEADER_X_REAL_IP);
    }

    /**
     * Берем первый из x-forwarded-for
     * @param ServerRequest $serverRequest
     * @return mixed
     */
    private function _ipFromXForwardedFor(ServerRequest $serverRequest)
    {
        $ips = $serverRequest->getHeaderLine(ServerRequest::HEADER_X_FORWARDED_FOR);
        $ips = explode(',', $ips);
        return count($ips) > 1 ? $ips[0] : null;
    }

    private function _ipFromForwarded(ServerRequest $serverRequest)
    {
        $ips = str_replace('for=', '', $serverRequest->getHeaderLine(ServerRequest::HEADER_FORWARDED));
        $ips = explode(',', $ips);
        return $ips[0];
    }

    /**
     * Из REMOTE_ADDR
     * @param ServerRequest $serverRequest
     * @return |null
     */
    private function _ipFromRemoteAddr(ServerRequest $serverRequest)
    {
        return $serverRequest->getServerParam(ServerRequest::REMOTE_ADDR);
    }

    /**
     * Заголовок CloudFlare
     * @param ServerRequest $serverRequest
     * @return string
     */
    private function _ipFromCfConnectingIp(ServerRequest $serverRequest)
    {
        return $serverRequest->getHeaderLine(ServerRequest::HEADER_CF_CONNECTING_IP);
    }

    /**
     * Берем, что осталось...
     * @param ServerRequest $serverRequest
     * @return mixed
     */
    private function _lastHope(ServerRequest $serverRequest)
    {
        $ips = $serverRequest->getHeaderLine(ServerRequest::HEADER_X_FORWARDED_FOR);
        $ips = explode(',', $ips);
        return $ips[0];
    }

    /**
     * @param string $ip
     * @return bool
     */
    private function _isCorrectIpForHeader($ip)
    {
        if (empty($ip)) {
            return false;
        }

        // Отбрасываем локальные адреса
        if (strpos($ip, self::LOCAL_MASK_1) === 0 || strpos($ip, self::LOCAL_MASK_2) === 0) {
            return false;
        }

        return true;
    }
}
