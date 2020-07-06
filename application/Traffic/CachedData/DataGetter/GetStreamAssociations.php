<?php
namespace Traffic\CachedData\DataGetter;

use Core\Entity\Definition\EntityDefinition;
use Traffic\CachedData\Storage\StorageInterface;
use Traffic\Model\StreamLandingAssociation;
use Traffic\Model\StreamOfferAssociation;

class GetStreamAssociations implements DataGetterInterface
{
    private $_entityName;

    public static function cacheKeys()
    {
        return [
            StreamLandingAssociation::entityName() => 'S_L',
            StreamOfferAssociation::entityName() => 'S_O'
        ];
    }

    public function __construct($entityName)
    {
        if (!isset(static::cacheKeys()[$entityName])) {
            throw new \Exception("incorrect entityName {$entityName}");
        }
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
            case StreamLandingAssociation::entityName():
                return StreamLandingAssociation::definition();
            case StreamOfferAssociation::entityName():
                return StreamOfferAssociation::definition();
        }
        throw new \Exception("not implemented for {$entityName}");
    }

    public function name()
    {
        return $this->_entityName;
    }

    public function fallback($scope = null)
    {
        return static::definitionFor($this->_entityName)->repository()->all('stream_id = ' . $scope['stream_id']);
    }

    public function get(StorageInterface $storage, $scope = null)
    {
        if (empty($scope['stream_id'])) {
            throw new \Exception("No 'stream_id'");
        }
        $key = static::cacheKey($this->_entityName, $scope['stream_id']);
        $definition = static::definitionFor($this->_entityName);
        $service = $definition->service();
        $result = $storage->get($key);
        $associations = [];
        foreach ($result as $rawData) {
            $associations[] = $service->restore($rawData);
        }
        return $associations;
    }

    public static function cacheKey($entityName, $streamId)
    {
        return implode('_', [static::cacheKeys()[$entityName], $streamId]);
    }
}