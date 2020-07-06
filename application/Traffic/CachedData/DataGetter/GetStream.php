<?php
namespace Traffic\CachedData\DataGetter;

use Component\Streams\Repository\StreamRepository;
use Component\Streams\Service\StreamService;
use Traffic\CachedData\Storage\StorageInterface;

class GetStream implements DataGetterInterface
{
    const NAME = 'get_stream';
    const CACHE_KEY = 'STRM';

    public function name()
    {
        return self::NAME;
    }

    public function get(StorageInterface $storage, $scope = null)
    {
        if (!is_numeric($scope)) {
            throw new \Exception('$scope must be an ID');
        }

        $rawData = $storage->get(static::cacheKey($scope));
        return StreamService::instance()->restore($rawData);
    }

    public function fallback($scope = null)
    {
        return StreamRepository::instance()->find($scope);
    }

    public static function cacheKey($id)
    {
        return implode('_', [self::CACHE_KEY, $id]);
    }
}