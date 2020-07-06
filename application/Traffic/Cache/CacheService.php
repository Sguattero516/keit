<?php

namespace Traffic\Cache;

use Traffic\Service\AbstractService;

class CacheService extends AbstractService
{
    const COMMON = 'common';
    const LP_OFFER = 'landing_offer';
    const CACHED_DATA = 'PD';
    const DEVICE_DETECTOR = 'device_detector';
    const SETTINGS = 'settings';

    /**
     * @var Cache[]
     */
    private $_caches = [];

    public function setCache($name, $cache)
    {
        $this->_caches[$name] = $cache;
    }

    public function setCaches($caches)
    {
        return $this->_caches = array_merge($this->_caches, $caches);
    }

    /**
     * @return Cache
     */
    public function commonCache()
    {
        return $this->_caches[self::COMMON];
    }
    /**
     * @return Cache
     */
    public function settingsCache()
    {
        return $this->_caches[self::SETTINGS];
    }
    /**
     * @return Cache
     */
    public function lpOfferCache()
    {
        return $this->_caches[self::LP_OFFER];
    }
    /**
     * @return Cache
     */
    public function dataCache()
    {
        return $this->_caches[self::CACHED_DATA];
    }
    /**
     * @return Cache
     */
    public function deviceDetectorCache()
    {
        return $this->_caches[self::DEVICE_DETECTOR];
    }

    public function totalSize()
    {
        return array_reduce($this->_caches, function ($sum, Cache $cache) {
            return $sum + $cache->getSize();
        }, 0);
    }

    /**
     * Внимание! Эта команда удаляет все возможные кэши, включая закэшированные токены lp-offer
     */
    public function deleteAllCaches()
    {
        foreach ($this->_caches as $cache) {
            $cache->deleteAll();
        }
    }

    public function flushOldNamespacedCache()
    {
        foreach ($this->_caches as $cache) {
            $cache->flushOldNamespacesCache();
        }
    }
}
