<?php
namespace Traffic\Pipeline\Stage;

use Component\BotDetection\Service\UserBotListService;
use Component\Device\Repository\LanguagesRepository;
use Component\GeoDb\Repository\ConnectionTypesRepository;
use Traffic\Device\DeviceInfoType;
use Traffic\Device\DeviceType;
use Traffic\Device\Service\DeviceInfoService;
use Traffic\Device\Service\RealRemoteIpService;
use Traffic\Device\Service\IpInfoService;
use Traffic\Device\Service\ProxyService;
use Traffic\Device\Service\ReferrerParserService;
use Traffic\GeoDb\IpInfoType;
use Traffic\GeoDb\Repository\GeoDbRepository;
use Traffic\Logging\TrafficLogEntry;
use Traffic\Model\Click;
use Traffic\Model\Setting;
use Traffic\Pipeline\Payload;
use Traffic\RawClick;
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Request\ServerRequest;
use Traffic\Service\ConfigService;
use Traffic\Tools\Tools;

class BuildRawClickStage
{
    protected $_stages;

    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $request = $payload->getServerRequest();
        $rawClick = $payload->getRawClick();

        if (empty($request)) {
            throw new StageException('Empty request');
        }
        if (empty($rawClick)) {
            throw new StageException('Empty rawClick');
        }

        $this->_prepare($request, $rawClick, $logEntry);
        $this->_findLanguage($request, $rawClick, $logEntry);
        $this->_findOtherParams($request, $rawClick);
        $this->_findSeReferrer($request, $rawClick);
        $this->_findReferrer($request, $rawClick);
        $this->_findSource($request, $rawClick);
        $this->_findXRequestedWith($request, $rawClick);
        $this->_findSearchEngine($request, $rawClick);
        $this->_findKeyword($request, $rawClick);
        $this->_findDefaultKeyword($request, $rawClick);
        $this->_findCosts($request, $rawClick);
        $this->_findSubIds($request, $rawClick);
        $this->_findExtraParams($request, $rawClick);
        $this->_findIpInfo($request, $rawClick);
        $this->_findDeviceInfo($rawClick);
        $this->_checkIfBot($request, $rawClick, $logEntry);
        $this->_checkIfProxy($request, $rawClick, $logEntry);

        $payload->setRawClick($rawClick);
        return $payload;
    }

    private function _prepare(ServerRequest $request, RawClick $rawClick, TrafficLogEntry $logger)
    {
        if (!$rawClick->getDateTime()) {
            $rawClick->set('datetime', new \DateTime());
        }

        if (!$rawClick->getUserAgent()) {
            $rawClick->setUserAgent($request->getHeaderLine(ServerRequest::HEADER_USER_AGENT));
        }

        if (!$rawClick->getIpString() || $rawClick->getIpString() === '0.0.0.0') {
            $rawClick->setIpString(RealRemoteIpService::instance()->find($request));

            $logMessage = $this->_getIpsHeadersString($request);
            $logger->add($logMessage);
        }
    }

    private function _checkIfBot(ServerRequest $request, RawClick $rawClick, TrafficLogEntry $logger)
    {
        if ($rawClick->get(IpInfoType::BOT_TYPE)) {
            $rawClick->setBot(true);
            return;
        }

        if (!$rawClick->isBot()) {
            $status = UserBotListService::instance()->isBot(
                $rawClick->getUserAgent(), $rawClick->getIpString(), [
                'check_bot_ip' => CachedSettingsRepository::instance()->get('check_bot_ip'),
                'check_bot_ua' => CachedSettingsRepository::instance()->get('check_bot_ua'),
                'check_bot_referer' => CachedSettingsRepository::instance()->get('check_bot_referer'),
                'check_bot_empty_ua' => CachedSettingsRepository::instance()->get('check_bot_empty_ua'),
            ], $logger);
            $rawClick->setBot($status);
        }
    }

    private function _checkIfProxy(ServerRequest $request, RawClick $rawClick, TrafficLogEntry $logger)
    {
        if (GeoDbRepository::instance()->isDataTypeAvailable(IpInfoType::PROXY_TYPE)) {
            if ($proxyType = $rawClick->get(IpInfoType::PROXY_TYPE)) {
                $rawClick->setIsUsingProxy(true);
                $logger->add("Proxy detected: " . $proxyType);
                return;
            }
        }

        if (ProxyService::instance()->usingProxy($request)) {
            $rawClick->setIsUsingProxy(true);
            $logger->add("WebProxy detected");
        }
    }

    private function _findIpInfo(ServerRequest $request, RawClick $rawClick)
    {
        $info = IpInfoService::instance()->getIpInfo($rawClick->getIpString());
        foreach ($info as $dataType => $value) {
            $rawClick->set($dataType, $value);
        }

        $rawClick->set('is_geo_resolved', true);
        $rawClick->set('is_isp_resolved', true);
    }

    private function _findDeviceInfo(RawClick $rawClick)
    {
        $info = DeviceInfoService::instance()->info($rawClick->getUserAgent());
        foreach ($info as $dataType => $value) {
            $rawClick->set($dataType, $value);
        }

        // Если не выявили тип устройства и само устройство телефон, то это вероятно Wifi подключение
        if (!$rawClick->get(IpInfoType::CONNECTION_TYPE) &&
            $rawClick->get(DeviceInfoType::DEVICE_TYPE) == DeviceType::DEVICE_MOBILE) {
            $rawClick->set(IpInfoType::CONNECTION_TYPE, ConnectionTypesRepository::WIFI);
        }

        $rawClick->set('is_device_resolved', true);
    }

    private function _findLanguage(ServerRequest $request, RawClick $rawClick, TrafficLogEntry $logger)
    {
        if ($rawClick->getLanguage()) {
            return;
        }

        $lang = ($request->getHeaderLine(ServerRequest::HEADER_ACCEPT_LANGUAGE) ? substr($request->getHeaderLine(ServerRequest::HEADER_ACCEPT_LANGUAGE), 0, 2) : '');
        $lang = strtoupper($lang);
        if (!LanguagesRepository::instance()->isValid($lang)) {
            $logger->add('Invalid language: "' . $lang . '"" from "' . $request->getHeaderLine(ServerRequest::HEADER_ACCEPT_LANGUAGE) . '"');
            $lang = '';
        }
        $rawClick->setLanguage($lang);
    }

    private function _findReferrer(ServerRequest $request, RawClick $rawClick)
    {
        if (ConfigService::instance()->isReferrerRedefineAllowed()) {
            $referrer = $request->getParam('referrer');
            if (!empty($referrer)) {
                $rawClick->setReferrer(urldecode($referrer));
                return;
            }
        }
        if (!$rawClick->getReferrer()) {
            if ($request->getHeaderLine(ServerRequest::HEADER_REFERER)) {
                $rawClick->setReferrer(urldecode($request->getHeaderLine(ServerRequest::HEADER_REFERER)));
            }
        }
    }

    private function _findSeReferrer(ServerRequest $request, RawClick $rawClick)
    {
        if ($rawClick->getSeReferrer()) {
            return;
        }

        $seRefferer = $request->getParam('se_referrer');
        if (!empty($seRefferer)) {
            $seReferrer = urldecode($seRefferer);
            $rawClick->setSeReferrer($seReferrer);
        }
    }

    private function _findSource(ServerRequest $request, RawClick $rawClick)
    {
        if ($rawClick->getSource()) {
            return;
        }

        if ($request->hasParam('source')) {
            $rawClick->setSource($request->getParam('source'));
            return;
        }

        if (!$rawClick->getSource() && $rawClick->getReferrer()) {
            if (preg_match('/http[s]?:\/\/(.*?)\//si', $rawClick->getReferrer(), $matches)) {
                $rawClick->setSource($matches[1]);
            }
        }
    }

    private function _findXRequestedWith(ServerRequest $request, RawClick $rawClick)
    {
        if ($rawClick->getXRequestedWith()) {
            return;
        }

        $value = $request->getHeaderLine("X-Requested-With");
        if (!empty(trim($value))) {
            $rawClick->setXRequestedWith($value);
        }
    }

    private function _findKeyword(ServerRequest $request, RawClick $rawClick)
    {
        if ($rawClick->getKeyword()) {
            return;
        }
        $keyword = $request->getParam('keyword');
        if (!empty($keyword)) {
            $keyword = urldecode($keyword);
        }

        $charset = ConfigService::instance()->get('system', 'keywords_charset', 'utf-8');

        if ($request->hasParam('charset')) {
            $charset = $request->getParam('charset');
        }

        if (isset($keyword)) {
            $encodedKeyword = @iconv($charset, 'UTF-8', $keyword);
            if ($encodedKeyword) {
                $keyword = $encodedKeyword;
            }
        }

        if (isset($keyword)) {
            $rawClick->setKeyword($keyword);
        }

        if ($rawClick->getSeReferrer() && !$rawClick->getKeyword()) {
            $keyword = ReferrerParserService::instance()->parse($rawClick->getSeReferrer());

            if ($keyword) {
                $rawClick->setKeyword($keyword);
            }
        }

        if ($rawClick->getReferrer() && !$rawClick->getKeyword()) {
            $keyword = ReferrerParserService::instance()->parse($rawClick->getReferrer());
            if ($keyword) {
                $rawClick->setKeyword($keyword);
            }
        }
    }

    private function _findDefaultKeyword(ServerRequest $request, RawClick $rawClick)
    {
        if (!$rawClick->getKeyword() && $request->getParam('default_keyword')) {
            $keyword = urldecode($request->getParam('default_keyword'));
            if ($request->getParam('charset')) {
                $encodedKeyword = @iconv($request->getParam('charset'), 'UTF-8', $keyword);
                if ($encodedKeyword) {
                    $keyword = $encodedKeyword;
                }
            }

            if (isset($keyword)) {
                $rawClick->setKeyword($keyword);
            }
        }
        return $this;
    }


    private function _findSearchEngine(ServerRequest $request, RawClick $rawClick)
    {
        if ($rawClick->getSearchEngine()) {
            return ;
        }
        $se = $request->getParam('se');
        if (!empty($se)) {
            $rawClick->setSearchEngine(urldecode($se));
        }

        if (!$rawClick->getSearchEngine()) {
            $vars = @parse_url($rawClick->getSeReferrer());
            $searchEngine = isset($vars['host']) ? $vars['host'] : null;

            if ($searchEngine) {
                $rawClick->setSearchEngine($searchEngine);
            }
        }
    }

    private function _findCosts(ServerRequest $request, RawClick $rawClick)
    {
        $cost = $request->getParam('cost');
        if (!is_null($cost)) {
            $rawClick->set('cost', Tools::utf8ize($cost));
        }
        $currency = $request->getParam('currency');
        if (!is_null($currency)) {
            $rawClick->set('currency', Tools::utf8ize($currency));
        }
    }

    private function _findSubIds(ServerRequest $request, RawClick $rawClick)
    {
        for ($i = 1; $i <= Click::getSubIdCount(); $i++) {
            $subId = $request->getParam('sub_id_' . $i);
            if (!is_null($subId)) {
                $rawClick->setSubIdN($i, urldecode($subId));
            }
            $subId = $request->getParam('subid' . $i);
            if (!is_null($subId)) {
                $rawClick->setSubIdN($i, urldecode($subId));
            }
        }
    }

    private function _findExtraParams(ServerRequest $request, RawClick $rawClick)
    {
        for ($i = 1; $i <= Click::EXTRA_PARAM_COUNT; $i++) {
            $extraParam = $request->getParam('extra_param_' . $i);
            if (!empty($extraParam)) {
                $rawClick->setExtraParam($i, urldecode($extraParam));
            }
        }
    }

    private function _findOtherParams(ServerRequest $request, RawClick $rawClick)
    {
        if ($request->getParam('lp_id')) {
            $rawClick->set('landing_id', $request->getParam('lp_id'));
        } else if ($request->getParam('landing_url')) {
            $rawClick->setLandingUrl($request->getParam('landing_url'));
        }
        $params = ['landing_id', 'creative_id', 'ad_campaign_id', 'external_id'];
        foreach ($params as $paramName) {
            $paramValue = $request->getParam($paramName);
            if (!empty($paramValue)) {
                $rawClick->set($paramName, $paramValue);
            }
        }
    }

    private function _getIpsHeadersString(ServerRequest $request)
    {
        $xffHeader = $request->getHeaderLine(ServerRequest::HEADER_X_FORWARDED_FOR);
        $cfcipHeader = $request->getHeaderLine(ServerRequest::HEADER_CF_CONNECTING_IP);
        $xripHeader = $request->getHeaderLine(ServerRequest::HEADER_X_REAL_IP);

        return 'Possible IP headers: ' .
            ServerRequest::HEADER_X_FORWARDED_FOR . ': ' . ($xffHeader ?: 'Empty') .
            '; ' . ServerRequest::HEADER_CF_CONNECTING_IP . ': ' . ($cfcipHeader ?: 'Empty') .
            '; ' . ServerRequest::HEADER_X_REAL_IP . ': ' . ($xripHeader ?: 'Empty');
    }
}
