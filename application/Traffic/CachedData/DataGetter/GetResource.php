<?php
namespace Traffic\CachedData\DataGetter;

use Core\Entity\Definition\EntityDefinition;
use Traffic\CachedData\Storage\StorageInterface;
use Traffic\Model\AffiliateNetwork;
use Traffic\Model\Landing;
use Traffic\Model\Offer;
use Traffic\Model\TrafficSource;

class GetResource implements DataGetterInterface
{
    const AFFILIATE_NETWORK = 'affiliate_network';
    const OFFER = 'offer';
    const LANDING = 'landing';
    const TRAFFIC_SOURCE = 'ts';

    private $_entityName;

    public function __construct($entityName)
    {
        $this->_entityName = $entityName;
    }

    /**
     * @param $entityName
     * @return EntityDefinition
     * @throws \Exception
     */
    public static function definitionFor($entityName)
    {
        switch ($entityName) {
            case Landing::entityName():
                return Landing::definition();
            case Offer::entityName():
                return Offer::definition();
            case AffiliateNetwork::entityName():
                return AffiliateNetwork::definition();
            case TrafficSource::entityName():
                return TrafficSource::definition();
        }
        throw new \Exception("no repo for " . $entityName);
    }

    public static function cacheKeys()
    {
        return [
            Landing::entityName() => 'LN',
            Offer::entityName() => 'OF',
            AffiliateNetwork::entityName() => 'AN',
            TrafficSource::entityName() => 'TS'
        ];
    }

    public function name()
    {
        return $this->_entityName;
    }

    public function get(StorageInterface $storage, $scope = null)
    {
        if (!is_numeric($scope)) {
            throw new \Exception('$scope must be an ID');
        }
        $result = $storage->get(static::cacheKey($this->name(), $scope));
        $service = static::definitionFor($this->name())->service();
        return $service->restore($result);
    }

    public function fallback($scope = null)
    {
        return self::definitionFor($this->_entityName)->repository()->find($scope);
    }

    public static function cacheKey($entityName, $id)
    {
        if (empty(static::cacheKeys()[$entityName])) {
            throw new \Exception('No type ' . $entityName);
        }
        return implode('_', [static::cacheKeys()[$entityName], $id]);
    }
}