<?php
namespace Traffic\Macros\Predefined;

use Traffic\Service\UrlService;
use Traffic\Macros\AbstractClickMacro;
use Traffic\Model\BaseStream;
use Traffic\RawClick;

class Offer extends AbstractClickMacro
{
    public function alwaysRaw()
    {
        return true;
    }

    public function process(BaseStream $stream, RawClick $rawClick, $offerId = null)
    {
        $url = UrlService::instance()->getBaseUrl($this->getServerRequest()->getUri(), 1) . '/?_lp=1';
        $url = UrlService::instance()->addParameterToUrl($url, '_token=' . $rawClick->get('token'));
        if ($offerId) {
            $url = UrlService::instance()->addParameterToUrl($url, '&offer_id=' . urlencode($offerId));
        }
        return $url;
    }
}