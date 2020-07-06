<?php
namespace Traffic\CachedData\DataGetter;

use Component\Campaigns\Repository\CampaignRepository;
use Core\Db\Db;
use Core\Entity\State;
use Traffic\CachedData\Storage\StorageInterface;

class GetCampaignAliases implements DataGetterInterface
{
    const NAME = 'campaign_aliases';
    const CACHE_KEY = 'CMPALIASES';

    public function name()
    {
        return self::NAME;
    }

    public function get(StorageInterface $storage, $scope = null)
    {
        return $storage->get(self::CACHE_KEY);
    }

    public function fallback($scope = null)
    {
        return static::buildHash();
    }

    public static function buildHash()
    {
        $where = 'state = ' . Db::quote(State::ACTIVE);
        $result = CampaignRepository::instance()->rawRows('id, alias', $where);
        $hash = [];
        foreach ($result as $row) {
            $hash[$row['alias']] = $row['id'];
        }
        return $hash;
    }
}