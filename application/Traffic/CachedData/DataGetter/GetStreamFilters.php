<?php
namespace Traffic\CachedData\DataGetter;

use Component\StreamFilters\Repository\StreamFilterRepository;
use Component\StreamFilters\Service\StreamFilterService;
use Traffic\CachedData\Storage\StorageInterface;

class GetStreamFilters implements DataGetterInterface
{
    const NAME = 'stream_filters';
    const CACHE_KEY = 'FLTRS';

    public function name()
    {
        return self::NAME;
    }

    public function get(StorageInterface $storage, $scope = null)
    {
        if (empty($scope['stream_id'])) {
            throw new \Exception("No 'stream_id'");
        }
        $key = static::cacheKey($scope['stream_id']);
        $result = $storage->get($key);
        $filters = [];
        foreach ($result as $rawData) {
            $filters[] = StreamFilterService::instance()->restore($rawData);
        }
        return $filters;
    }

    public function fallback($scope = null)
    {
        return StreamFilterRepository::instance()->all('stream_id = ' . (int) $scope['stream_id'], 'id');
    }

    public static function cacheKey($streamId)
    {
        return implode('_', [self::CACHE_KEY, $streamId]);
    }
}