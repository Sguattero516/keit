<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;
use Traffic\Repository\CachedCampaignRepository;

class ToCampaign extends AbstractAction
{
    protected $_weight = 6;

    public function getType()
    {
        return self::TYPE_OTHER;
    }

    public function getField()
    {
        return self::CAMPAIGNS;
    }

    protected function _execute()
    {
        $campaign = CachedCampaignRepository::instance()->findInCacheById($this->getRawActionPayload());
        if (!empty($campaign)) {
            $this->setDestinationInfo($campaign->getName());
        }
    }
}