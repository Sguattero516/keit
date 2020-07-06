<?php
namespace Traffic\CachedData\DataGetter;

use Component\Landings\Repository\LandingRepository;
use Core\Db\Db;
use Core\Entity\State;
use Traffic\CachedData\Storage\StorageInterface;

class GetLandingsUrls implements DataGetterInterface
{
    const NAME = 'landing_urls';
    const CACHE_KEY = 'L_URLS';

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
        $landings = LandingRepository::instance()->rawRows(
            'id, action_payload',
            'state <> ' . Db::quote(State::DELETED)
        );
        $urls = [];
        foreach ($landings as $landing) {
            $urls[$landing['id']] = $landing['action_payload'];
        }
        return $urls;
    }
}