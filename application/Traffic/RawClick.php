<?php
namespace Traffic;

use Traffic\Device\DeviceType;
use Traffic\Model\Click;

use Core\Currency\Service\CurrencyService;
use Component\Streams\Repository\StreamRepository;
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Service\SettingsService;
use Traffic\Repository\ParameterRepository;
use Core\Application\Exception\Error;
use Traffic\Logging\Service\LoggerService;
use Core\Model\AbstractModel;
use Traffic\Tools\Tools;

class RawClick implements RawClickInterface
{
    private $_stream;

    private $_seReferrer;

    private $_data = array();

    const REFERRER_LIMIT  = 250;

    const DESTINATION_LIMIT = 250;

    public function __construct($data = null)
    {
        if (isset($data) && is_array($data)) {
            $this->setData($data);
        }
        $this->set('is_geo_resolved', false);
        $this->set('is_device_resolved', false);
        $this->set('is_isp_resolved', false);
    }

    public static function build($data)
    {
        $obj = new RawClick($data);
        return $obj;
    }

    public function get($key)
    {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        } else {
            return '';
        }
    }

    public function getData()
    {
        return $this->_data;
    }

    private function _checkInFields($key)
    {
        if ($key == 'datetime' && is_string($this->_data['datetime'])) {
            $this->_data['datetime'] = new \DateTime($this->_data['datetime']);
        }
    }

    public function set($key, $value)
    {
        $this->_data[$key] = $value;
        $this->_checkInFields($key);
        return $this;
    }

    public function setData($data)
    {
        if (empty($this->_data)) {
            $this->_data = $data;
        } else {
            $this->_data = array_merge($this->_data, $data);
        }
        foreach($data as $key => $val) {
            $this->_checkInFields($key);
        }
        return $this;
    }

    /**
     * @deprecated
     */
    public function restoreData($data)
    {
        $this->setData($data);
    }

    public function getParentCampaignId()
    {
        return $this->get('parent_campaign_id');
    }

    public function setParentCampaignId($id)
    {
        $this->set('parent_campaign_id', $id);
        return $this;
    }

    public function setParentSubId($subId)
    {
        $this->set('parent_sub_id', $subId);
        return $this;
    }

    public function setIpString($ip)
    {
        $this->set('ip_string', $ip);
        $this->setIp(Tools::ip2long($ip));
    }

    public function setUserAgent($ua)
    {
        $this->set('user_agent', $ua);
    }

    public function setLandingUrl($url)
    {
        $this->set('landing_url', $url);
    }

    public function getLandingUrl()
    {
        return $this->get('landing_url');
    }

    public function setIp($ip)
    {
        $this->set('ip', $ip);
        return $this;
    }

    public function setVisitorCode($code)
    {
        $this->set('visitor_code', $code);
    }

    public function setCountry($country)
    {
        $this->set('country', $country);
        return $this;
    }

    public function setRegion($region)
    {
        $this->set('region', $region);
        return $this;
    }

    public function setCity($city)
    {
        $this->set('city', $city);
        return $this;
    }

    public function setBrowser($browser)
    {
        $this->set('browser', $browser);
        return $this;
    }

    public function setOs($os)
    {
        $this->set('os', $os);
        return $this;
    }

    public function setSearchEngine($se)
    {
        $this->set('search_engine', $se);
        return $this;
    }

    public function setReferrer($referrer)
    {
        $this->set('referrer', $referrer);
        return $this;
    }

    public function setSeReferrer($referer)
    {
        $this->_seReferrer = (string)$referer;
        return $this;
    }

    public function setIsMobile($value)
    {
        $this->set('is_mobile', $value);
        return $this;
    }

    public function setSource($source)
    {
        $this->set('source', $source);
        return $this;
    }

    public function setXRequestedWith($value)
    {
        $this->set('x_requested_with', $value);
    }

    public function setIsp($value)
    {
        $this->set('isp', $value);
        return $this;
    }

    public function setOperator($value)
    {
        $this->set('operator', $value);
        return $this;
    }

    public function setKeyword($keyword)
    {
        $this->set('keyword', $keyword);
        return $this;
    }

    public function setLanguage($lang)
    {
        $this->set('language', strtoupper($lang));
        return $this;
    }

    public function setBot($bot)
    {
        $this->set('is_bot', $bot);
        return $this;
    }

    public function setDateTime(\DateTime $dateTime)
    {
        $this->set('datetime', $dateTime);
        return $this;
    }

    public function setSubId($value, $test = null)
    {
        if (!empty($test)) {
            throw new Error('Use setSubIdN');
        }
        $this->set('sub_id', $value);
        return $this;
    }

    public function setSubIdN($num, $value = null)
    {
        $this->set('sub_id_' . $num, $value);
        return $this;
    }

    public function setCost($value, $currency = null)
    {
        if (!empty($currency) && $currency != CachedSettingsRepository::instance()->get('currency')) {
            $value = CurrencyService::instance()->exchangeToCurrent($value, $currency);
        }

        $this->set('cost', $value);
    }

    public function setLandingId($id)
    {
        $this->set('landing_id', $id);
    }

    public function setOfferId($id)
    {
        $this->set('offer_id', $id);
    }

    public function setAffiliateNetworkId($id)
    {
        $this->set('affiliate_network_id', $id);
    }

    public function getAffiliateNetworkId()
    {
        return $this->get('affiliate_network_id');
    }

    public function setRevenue($revenue)
    {
        if ($this->isLead()) {
            $this->set('lead_revenue', $revenue);
        }

        if ($this->isSale()) {
            $this->set('sale_revenue', $revenue);
        }

        if ($this->isRejected()) {
            $this->set('rejected_revenue', $revenue);
        }

        return $this;
    }

    public function isLead()
    {
        return $this->get('is_lead');
    }

    public function isSale()
    {
        return $this->get('is_sale');
    }

    public function isRejected()
    {
        return $this->get('is_rejected');
    }

    public function getCost()
    {
        return $this->get('cost');
    }

    public function getProfit()
    {
        return $this->getRevenue() - $this->getCost();
    }

    public function getLandingId()
    {
        return $this->get('landing_id');
    }

    public function getOfferId()
    {
        return $this->get('offer_id');
    }

    public function setStreamId($id)
    {
        $this->set('stream_id', $id);
        return $this;
    }

    public function setCampaignId($id)
    {
        $this->set('campaign_id', $id);
        return $this;
    }

    public function setDestination($destination)
    {
        $this->set('destination', $destination);
        return $this;
    }

    public function getStreamId()
    {
        return $this->get('stream_id');
    }

    /**
     * @deprecated
     */
    public function getStream()
    {
        if (!$this->_stream) {
            $this->_stream = StreamRepository::instance()->find($this->get('stream_id'));
        }
        return $this->_stream;
    }

    public function getRevenue()
    {
        return (float) $this->get('lead_revenue') + (float) $this->get('sale_revenue') + (float) $this->get('rejected_revenue');
    }

    public function getIp()
    {
        return $this->get('ip');
    }

    public function getIpString()
    {
        if (!$this->get('ip_string')) {
            $this->set('ip_string', long2ip((int) $this->getIp()));
        }
        return $this->get('ip_string');
    }

    public function getCountry()
    {
        return $this->get('country');
    }

    public function getRegion()
    {
        return $this->get('region');
    }

    public function getCity()
    {
        return $this->get('city');
    }

    public function getLanguage()
    {
        return $this->get('language');
    }

    // @deprecated
    public function getLang()
    {
        return $this->getLanguage();
    }

    public function getBrowser()
    {
        return $this->get('browser');
    }

    public function getBrowserVersion()
    {
        return $this->get('browser_version');
    }

    public function getIsp()
    {
        return $this->get('isp');
    }

    public function getConnectionType()
    {
        return $this->get('connection_type');
    }

    public function getOperator()
    {
        return $this->get('operator');
    }

    public function getOs()
    {
        return $this->get('os');
    }

    public function getOsVersion()
    {
        return $this->get('os_version');
    }
    public function getSearchEngine()
    {
        return $this->get('search_engine');
    }

    public function getDateTime()
    {
        return $this->get('datetime');
    }

    public function getKeyword()
    {
        return $this->get('keyword');
    }

    public function getSource()
    {
        return $this->get('source');
    }

    public function getXRequestedWith()
    {
        return $this->get('x_requested_with');
    }

    public function getUserAgent()
    {
        return $this->get('user_agent');
    }

    public function isMobile()
    {
        return in_array($this->getDeviceType(), [DeviceType::DEVICE_TABLET, DeviceType::DEVICE_MOBILE]);
    }

    public function isUsingProxy()
    {
        return $this->get('is_using_proxy');
    }

    public function isGeoResolved()
    {
        return $this->get('is_geo_resolved');
    }

    public function isDeviceResolved()
    {
        return $this->get('is_device_resolved');
    }

    public function isIspResolved()
    {
        return $this->get('is_isp_resolved');
    }

    public function isProcessed()
    {
        return $this->get('processed');
    }

    public function getReferer()
    {
        return $this->getReferrer();
    }

    public function getReferrer()
    {
        return $this->get('referrer');
    }

    public function getSeReferrer()
    {
        return $this->_seReferrer;
    }

    public function getParentSubId()
    {
        return $this->get('parent_sub_id');
    }

    public function getSubId($num = null)
    {
        if (!empty($num)) {
            LoggerService::instance()->error('Please use Visitor->getSubIdN() instead of Visitor->getSubId()');
            return $this->getSubIdN($num);
        }
        return $this->get('sub_id');
    }

    public function getSubIdN($num = null)
    {
        if (!isset($num)) {
            return $this->get('sub_id');
        } else {
            return $this->get('sub_id_' . $num);
        }
    }

    // @deprecated
    public function getSubId2()
    {
        return $this->getSubIdN(2);
    }

    public function getExternalId()
    {
        return $this->get('external_id');
    }

    public function getAdCampaignId()
    {
        return $this->get('ad_campaign_id');
    }

    public function getCreativeId()
    {
        return $this->get('creative_id');
    }

    public function getExtraParam($num)
    {
        return $this->get('extra_param_' . $num);
    }

    public function setExtraParam($num, $value)
    {
        $this->set('extra_param_' . $num, $value);
        return $this;
    }

    public function getDeviceModel()
    {
        return $this->get('device_model');
    }

    public function getDeviceBrand()
    {
        return $this->get('device_brand');
    }

    public function getDeviceType()
    {
        return $this->get('device_type');
    }

    public function getDestination()
    {
        return $this->get('destination');
    }

    public function getTsId()
    {
        return $this->get('ts_id');
    }

    public function isUniqueCampaign()
    {
        return $this->get('is_unique_campaign');
    }

    public function isUniqueGlobal()
    {
        return $this->get('is_unique_global');
    }

    public function isUniqueStream()
    {
        return $this->get('is_unique_stream');
    }

    public function setIsUsingProxy($value)
    {
        $this->set('is_using_proxy', $value);
        return $this;
    }

    public function isBot()
    {
        return $this->get('is_bot');
    }

    public function getVisitorCode()
    {
        return $this->get('visitor_code');
    }

    public function getCampaignId()
    {
        return $this->get('campaign_id');
    }

    public function setToken($token)
    {
        $this->set('token', $token);
        return $this;
    }

    public function getToken()
    {
        return $this->get('token');
    }

    public function alwaysEmptyCookies()
    {
        return $this->get('always_empty_cookies');
    }

    public function serialize()
    {
        $referrer = $this->getReferer();

        $data = array(
            'visitor_code' => $this->getVisitorCode(),
            'campaign_id' => $this->getCampaignId(),
            'stream_id' => $this->getStreamId(),
            'destination' => $this->getFilteredDestination(),
            'landing_id' => $this->getLandingId(),
            'landing_url' => $this->getLandingUrl(),
            'offer_id' => $this->getOfferId(),
            'affiliate_network_id' => $this->getAffiliateNetworkId(),
            'ip' => $this->getIp(),
            'ip_string' => $this->getIpString(),
            'datetime' => $this->getDateTime()->format(AbstractModel::DATETIME_FORMAT),
            'user_agent' => $this->getUserAgent(),

            'language' => substr($this->getLanguage(), 0, 2),
            'source' => $this->getSource(),
            'x_requested_with' => $this->getXRequestedWith(),
            'keyword' => $this->getKeyword(),
            'referrer' => mb_substr($this->getReferrer(), 0, self::REFERRER_LIMIT),
            'search_engine' => $this->getSearchEngine(),

            'is_mobile' => ($this->isMobile() ? 1 : 0),
            'is_bot' => ($this->isBot() ? 1 : 0),
            'is_using_proxy' => ($this->isUsingProxy() ? 1 : 0),
            'is_empty_referrer' => (empty($referrer)),

            'is_unique_campaign' => ($this->isUniqueCampaign() ? 1 : 0),
            'is_unique_stream' => ($this->isUniqueStream() ? 1 : 0),
            'is_unique_global' => ($this->isUniqueGlobal() ? 1 : 0),

            'is_geo_resolved' => ($this->isGeoResolved() ? 1 : 0),
            'is_device_resolved' => ($this->isDeviceResolved() ? 1 : 0),
            'is_isp_resolved' => ($this->isIspResolved() ? 1 : 0),

            'cost' => $this->getCost(),
            'sub_id' => $this->getSubId(),
            'parent_campaign_id' => $this->get('parent_campaign_id'),
            'parent_sub_id' => $this->getParentSubId(),
            'is_sale' => (int) $this->isSale(),
            'is_lead' => (int) $this->isLead(),
            'is_rejected' => (int) $this->isRejected(),
            'lead_revenue' => $this->get('lead_revenue'),
            'sale_revenue' => $this->get('sale_revenue'),
            'rejected_revenue' => $this->get('rejected_revenue'),
        );

        for ($i = 1; $i <= Click::getSubIdCount(); $i++) {
            $data['sub_id_' . $i] = $this->getSubIdN($i);
        }

        for ($i = 1; $i <= Click::EXTRA_PARAM_COUNT; $i++) {
            $data['extra_param_' . $i] = $this->getExtraParam($i);
        }

        if ($this->isGeoResolved()) {
            $data['country'] = $this->getCountry();
            $data['region'] = $this->getRegion();
            $data['city'] = $this->getCity();
        }

        if ($this->isIspResolved()) {
            $data['operator'] = $this->getOperator();
            $data['isp'] = $this->getIsp();
            $data['connection_type'] = $this->getConnectionType();
        }

        if ($this->isDeviceResolved()) {
            $data['browser'] = $this->getBrowser();
            $data['browser_version'] = $this->getBrowserVersion();
            $data['os'] = $this->getOs();
            $data['os_version'] = $this->getOsVersion();
            $data['device_model'] = $this->getDeviceModel();
            $data['device_type'] = $this->getDeviceType();
            $data['device_brand'] = $this->getDeviceBrand();
            $data['is_mobile'] = ($this->isMobile() ? 1 : 0);
        }

        $tokenParams = ParameterRepository::getAvailableParameters(); // TODO check that
        $tokenParams[] = 'ts_id';
        foreach ($tokenParams as $tokenParam) {
            $param = $this->get($tokenParam);
            if (!is_null($param)) {
                $data[$tokenParam] = $param;
            }
        }
        return $data;
    }

    public function getFilteredDestination()
    {
        $destination = $this->getDestination();
        $destination = preg_replace('/_token=(.*?)$/', '_token=[filtered]', $destination);
        if (is_string($destination) && mb_strlen($destination) > self::DESTINATION_LIMIT) {
            $destination = mb_substr($destination, 0, self::DESTINATION_LIMIT, 'utf-8') . '...';
        }

        return $destination = mb_substr($destination, 0, self::DESTINATION_LIMIT, 'utf-8');
    }
}