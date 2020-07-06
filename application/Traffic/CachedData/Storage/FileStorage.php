<?php
namespace Traffic\CachedData\Storage;

use Traffic\Cache\Cache;
use Traffic\Cache\CacheService;
use Traffic\Cache\NoCache;
use Traffic\Logging\Service\LoggerService;

class FileStorage implements StorageInterface
{
    /**
     * @var Cache
     */
    private $_cache;

    public function __construct()
    {
        $this->_cache = CacheService::instance()->dataCache();
    }

    public function set($key, $data)
    {
        LoggerService::instance()->debug(
            '[FileStorage] Save key ' . $key . ' '
            . (empty($data) ? '(empty value)' : '')
        );
        $this->_cache->set($key, $data);
    }

    public function commit()
    {
        // nothing
    }

    /**
     * @param string $key
     * @return array|false|mixed|object|string
     * @throws NoCache
     */
    public function get($key)
    {
        LoggerService::instance()->debug('[FileStorage] Read key ' . $key);
        return $this->_cache->get($key);
    }

    public function delete($key)
    {
        LoggerService::instance()->debug('[FileStorage] Delete ' . $key);
        $this->_cache->delete($key);
    }

    public function deleteAll()
    {
        LoggerService::instance()->debug('[FileStorage] Delete all');
        $this->_cache->deleteAll();
    }

    public function exists($key)
    {
        return $this->_cache->isExists($key);
    }

    public function info()
    {
        return $this->_cache->getStats();
    }

    public function size()
    {
        return $this->_cache->getSize();
    }
}