<?php
namespace Traffic;

interface RawClickInterface
{
    public function get($key);

    public function getData();

    public function getParentCampaignId();

    public function getLandingUrl();

    public function getAffiliateNetworkId();

    public function isLead();

    public function isSale();

    public function isRejected();

    public function getCost();

    public function getProfit();

    public function getLandingId();

    public function getOfferId();

    public function getStreamId();

    public function getRevenue();

    public function getIp();

    public function getIpString();

    public function getCountry();

    public function getRegion();

    public function getCity();

    public function getLanguage();

    public function getBrowser();

    public function getBrowserVersion();

    public function getIsp();

    public function getConnectionType();

    public function getOperator();

    public function getOs();

    public function getOsVersion();

    public function getSearchEngine();

    public function getDateTime();

    public function getKeyword();

    public function getSource();

    public function getXRequestedWith();

    public function getUserAgent();

    public function isMobile();

    public function isUsingProxy();

    public function isGeoResolved();

    public function isDeviceResolved();

    public function isIspResolved();

    public function isProcessed();

    public function getReferer();

    public function getReferrer();

    public function getParentSubId();

    public function getSubId();

    public function getSubIdN($num = null);

    public function getExternalId();

    public function getAdCampaignId();

    public function getCreativeId();

    public function getExtraParam($num);

    public function getDeviceModel();

    public function getDeviceBrand();

    public function getDeviceType();

    public function getDestination();

    public function getTsId();

    public function isUniqueCampaign();

    public function isUniqueGlobal();

    public function isUniqueStream();

    public function isBot();

    public function getVisitorCode();

    public function getCampaignId();

    public function getToken();
}
