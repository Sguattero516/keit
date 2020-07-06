<?php
namespace Traffic\Repository;

use Traffic\CachedData\DataGetter\GetCampaign;
use Traffic\CachedData\DataGetter\GetCampaignAliases;
use Traffic\CachedData\DataGetter\GetCampaignTokens;
use Traffic\CachedData\Repository\CachedDataRepository;

class CachedCampaignRepository extends AbstractBaseRepository
{
    public function findByToken($token)
    {
        $hash = CachedDataRepository::instance()->get(GetCampaignTokens::NAME);
        if (isset($hash[$token])) {
            return $this->findInCacheById($hash[$token]);
        }
        return null;
    }

    public function findByAlias($alias)
    {
        $hash = CachedDataRepository::instance()->get(GetCampaignAliases::NAME);
        if (isset($hash[$alias])) {
            return $this->findInCacheById($hash[$alias]);
        }
        return null;
    }

    public function findInCacheById($id)
    {
        return CachedDataRepository::instance()->get(GetCampaign::NAME, $id);
    }
}