<?php
namespace Traffic\Cache;

use Core\Application\Exception\Error;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\VoidCache;
use Traffic\Redis\Service\RedisStorageService;

/**
 * Абстракция над DoctrineCache.
 * Напрямую с этим классом не работаем, используем CacheService
 */
class Cache
{
    const STORAGE_OFF = 'off';
    const STORAGE_APC = 'apc';
    const STORAGE_AUTO = 'auto';
    const STORAGE_SQLITE = 'sqlite';
    const STORAGE_FILES = 'files';
    const STORAGE_REDIS = 'redis';
    const STORAGE_MEMCACHE = 'memcache';
    const STORAGE_XCACHE = 'xcache';
    const STORAGE_MEMCACHED = 'memcached';
    const STORAGE_ARRAY = 'array';
    const CACHE_DIRECTORY = 'cache_directory';

    const DEFAULT_CACHE_DIR = '/var/cache';

    private $_name;

    /**
     * @var string
     */
    private $_storageName;

    /**
     * @var CacheProvider
     */
    private $_doctrineCache;

    /**
     * @var array
     */
    private $_options;

    const VALIDATION_TIMESTAMP = 'TIMESTAMP';

    public function __construct($name, $storage, $opts = [])
    {
        $this->_name = $name;
        $this->_storageName = $storage;
        $this->_options = $opts;
        $this->_rebuildDoctrineCache();
    }

    public function getStorageName()
    {
        return $this->_storageName;
    }

    public function getDoctrineCache()
    {
        return $this->_doctrineCache;
    }

    /**
     * @param $name string
     * @param $value string
     * @return $this
     * @throws CacheError
     * @throws Error
     */
    public function setOption($name, $value)
    {
        $this->_options[$name] = $value;
        $this->_rebuildDoctrineCache();
        return $this;
    }

    public function getOption($name)
    {
        return isset($this->_options[$name]) ? $this->_options[$name] : null ;
    }

    private function _getNamespace()
    {
        return strtoupper($this->_name) . '_' . $this->_getNamespaceHash();
    }

    private function _getNamespaceHash()
    {
        return strtoupper(substr(md5(SALT), 10, 5));
    }

    public function set($key, $value, $ttl = 0)
    {
        return $this->_doctrineCache->save($key, $value, $ttl);
    }

    /**
     * @param $key
     * @return false|mixed
     * @throws NoCache
     */
    public function get($key)
    {
        $result = $this->_doctrineCache->fetch($key);
        if ($result === false) {
            throw new NoCache("no key '{$key}'");
        }
        return $result;
    }

    public function delete($key)
    {
        return $this->_doctrineCache->delete($key);
    }

    public function isExists($key)
    {
        return $this->_doctrineCache->contains($key);
    }

    public function deleteAll()
    {
        return $this->_doctrineCache->deleteAll();
    }

    public function flushAll()
    {
        return $this->_doctrineCache->flushAll();
    }

    public function getStats()
    {
        return $this->_doctrineCache->getStats();
    }

    public function getSize()
    {
        $stats = $this->getStats();
        if (!empty($stats['memory_usage'])) {
            return $stats['memory_usage'];
        }
        return 0;
    }

    public function validateCache()
    {
        $time = time();
        $this->set(self::VALIDATION_TIMESTAMP, $time, CacheService::MAX_TTL);
        $checkTime = $this->get(self::VALIDATION_TIMESTAMP);

        if ($checkTime < $time) {
            throw new CacheInvalid('Cache storage is read-only! ' . $checkTime. '-'.$time);
        }
    }

    public function flushOldNamespacesCache()
    {
        $cleaner = FlushOldNamespacedCacheService::instance();
        $cleaner->init($this->getDoctrineCache(), $this->_getNamespaceHash());
        return $cleaner->flush();
    }

    /**
     * @return ArrayCache|FilesystemCache|RedisCache|VoidCache
     * @throws CacheError
     * @throws Error
     */
    private function _rebuildDoctrineCache()
    {
        switch ($this->_storageName) {
            case self::STORAGE_OFF:
                $cacheDriver = new VoidCache();
                break;

            case self::STORAGE_ARRAY:
                $cacheDriver = new ArrayCache();
                break;

            case self::STORAGE_REDIS:
                $redis = RedisStorageService::instance()->newRedisInstance();
                $cacheDriver = new RedisCache();
                $cacheDriver->setRedis($redis);
                break;

            case self::STORAGE_APC:
            case self::STORAGE_FILES:
            case self::STORAGE_MEMCACHE:
            case self::STORAGE_MEMCACHED:
            case self::STORAGE_XCACHE:
                $path = $this->getOption(self::CACHE_DIRECTORY);
                if (empty($path)) {
                    throw new CacheError("Option 'cache_directory' is empty");
                }

                $cacheDriver = new FilesystemCache($path);
                break;

            default:
                throw new CacheError("Storage '{$this->_storageName}' is not available");
        }

        $cacheDriver->setNamespace($this->_getNamespace());

        $this->_doctrineCache = $cacheDriver;
        return $this->_doctrineCache;
    }
}
