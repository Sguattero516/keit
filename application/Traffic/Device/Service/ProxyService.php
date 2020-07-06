<?php
namespace Traffic\Device\Service;

use Traffic\Request\ServerRequest;
use Traffic\Service\AbstractService;

class ProxyService extends AbstractService
{
    const LOCALHOST_IP = '127.0.0.1';

    private $_forceNoProxy = false;

    private $_proxyHeaders = array(
        ServerRequest::HEADER_X_FORWARDED_FOR,
        'X-FORWARDED',
        'PROXY-CONNECTION'
    );

    private $_proxyHeadersCheck = [
        'X-YANDEX-TURBO'
    ];

    private $_cloudFlareHeaders = [
        ServerRequest::HEADER_CF_CONNECTING_IP,
        'CF-IPCOUNTRY',
        'CF-RAY',
        'CF-VISITOR',
    ];

    public function forceNoProxy($value = true)
    {
        $this->_forceNoProxy = $value;
    }

    public function usingProxy(ServerRequest $request)
    {
        if ($this->_forceNoProxy) {
            return false;
        }

        $ip = $request->getServerParam(ServerRequest::ORIGINAL_REMOTE_ADDR);

        switch (true) {
            case $this->_isBehindCloudFlare($request) && $this->_isXffContainsCfcip($request):
                return $this->_hasSeveralIpsInXffHeader($request);
            case $this->_isBehindCloudFlare($request) && !$this->_isXffContainsCfcip($request):
                return false;
            case $this->_isBehindLocalProxy($ip):
                return $this->_hasSeveralIpsInXffHeader($request);
            default:
                return $this->_detectProxyUsageByHeaders($request, $ip);
        }
    }

    private function _isBehindLocalProxy($ip)
    {
        return $ip == self::LOCALHOST_IP;
    }

    private function _isBehindCloudFlare(ServerRequest $request)
    {
        foreach ($this->_cloudFlareHeaders as $header) {
            if ($request->hasHeader($header)) {
                return true;
            }
        }
        return false;
    }

    private function _isXffContainsCfcip(ServerRequest $request)
    {
        if ($request->hasHeader(ServerRequest::HEADER_X_FORWARDED_FOR) &&
                $request->hasHeader(ServerRequest::HEADER_CF_CONNECTING_IP) &&
                $request->getHeaderLine(ServerRequest::HEADER_X_FORWARDED_FOR) &&
                $request->getHeaderLine(ServerRequest::HEADER_CF_CONNECTING_IP)) {
            $ipsList = array_map('trim', explode(",", $request->getHeaderLine(ServerRequest::HEADER_X_FORWARDED_FOR)));
            return in_array($request->getHeaderLine(ServerRequest::HEADER_CF_CONNECTING_IP), $ipsList);
        }
        return false;
    }

    private function _hasSeveralIpsInXffHeader(ServerRequest $request)
    {
        $ipsList = array_map('trim', explode(",", $request->getHeaderLine(ServerRequest::HEADER_X_FORWARDED_FOR)));
        return sizeof(array_unique($ipsList)) > 1;
    }

    private function _detectProxyUsageByHeaders(ServerRequest $request, $ip)
    {
        foreach ($this->_proxyHeadersCheck as $header) {
            if ($request->getHeaderLine($header)) {
                return true;
            }
        }

        foreach ($this->_proxyHeaders as $header) {
            if ($request->getHeaderLine($header) && $request->getHeaderLine($header) != $ip) {
                return true;
            }
        }

        return false;
    }
}
