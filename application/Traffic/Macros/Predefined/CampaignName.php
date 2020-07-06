<?php
namespace Traffic\Macros\Predefined;

use Traffic\Repository\CachedCampaignRepository;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;
use Traffic\Model\BaseStream;

class CampaignName extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick)
    {
        $campaign = CachedCampaignRepository::instance()->findInCacheById($rawClick->getCampaignId());
        if ($campaign) {
            return $campaign->getName();
        }
    }
}