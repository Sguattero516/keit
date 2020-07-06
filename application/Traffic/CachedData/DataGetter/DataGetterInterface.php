<?php
namespace Traffic\CachedData\DataGetter;

use Traffic\CachedData\Storage\StorageInterface;
use Traffic\Cache\NoCache;

interface DataGetterInterface
{
    /**
     * @return string
     */
    public function name();
    /**
     * @param StorageInterface $storage
     * @param null|mixed $scope
     * @return mixed
     * @throws NoCache
     */
    public function get(StorageInterface $storage, $scope = null);

    /**
     * @param null|mixed $scope
     * @return mixed
     */
    public function fallback($scope = null);
}
