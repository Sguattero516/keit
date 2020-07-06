<?php
namespace Traffic\CachedData\DataGetter;

use Component\Campaigns\Service\CampaignService;
use Traffic\CachedData\Storage\StorageInterface;

class GetCampaign implements DataGetterInterface
{
    const NAME = 'campaign';
    const CACHE_KEY = 'CMPGN';

    public function name()
    {
        return self::NAME;
    }

    public static function cacheKey($id)
    {
        return self::CACHE_KEY . '_' . $id;
    }

    public function get(StorageInterface $storage, $scope = null)
    {
        if (!is_numeric($scope)) {
            throw new \Exception("ID must be a number");
        }
        $result = $storage->get(static::cacheKey($scope));
        if ($result === false) {
            return $result;
        }

        return CampaignService::instance()->restore($result);
    }

    // no fallback strategy
    public function fallback($scope = null)
    {
        return null;
    }
}