<?php
namespace Traffic\CachedData\Storage;

use Traffic\CachedData\Storage\StorageInterface;
use Traffic\Cache\NoCache;
use Traffic\Logging\Service\LoggerService;
use Traffic\Redis\Service\RedisStorageService;

class RedisStorage implements StorageInterface
{
    const CACHE_PREFIX = 'PD::';

    /**
     * @var RedisStorageService
     */
    private $_redisService;

    private $_delayed = [];

    private $_compressionEnabled = false;

    public function __construct()
    {
        $this->_redisService = RedisStorageService::instance();
    }

    public function enableCompression()
    {
        $this->_compressionEnabled = true;
    }

    public function set($key, $data)
    {
        LoggerService::instance()->debug('[RedisStorage] Save key ' . $key . ' ' . (empty($data) ? '(empty value)' : '') );
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($this->_compressionEnabled) {
            $data = gzcompress($data, 9);
        }
        $this->_delayed[] = [self::CACHE_PREFIX . $key, $data];
    }

    public function commit()
    {
        $pipe = $this->_redisService->multi(\Redis::PIPELINE);
        $prefix = $this->_redisService->prefix();
        foreach ($this->_delayed as $item) {
            $pipe->set($prefix . $item[0], $item[1]);
        }
        $pipe->exec();
        $this->_delayed = [];
    }

    public function get($key)
    {
        LoggerService::instance()->debug('[RedisStorage] Read key ' . $key);
        $result = $this->_redisService->get(self::CACHE_PREFIX . $key);
        if ($result === false) {
            throw new NoCache("no key '{$key}'");
        }
        if ($this->_compressionEnabled) {
            $result = gzuncompress($result);
        }
        $result = json_decode($result, JSON_OBJECT_AS_ARRAY);
        if (json_last_error()) {
            LoggerService::instance()->error('[RedisStorage] Error (' . json_last_error() . ') ' . json_last_error_msg());
        }
        return $result;
    }

    public function delete($key)
    {
        LoggerService::instance()->debug('[RedisStorage] Delete ' . $key);
        $this->_redisService->del(self::CACHE_PREFIX . $key);
    }

    public function deleteAll()
    {
        LoggerService::instance()->debug('[RedisStorage] Delete all');
        foreach ($this->_redisService->keys(self::CACHE_PREFIX . '*') as $key) {
            $this->_redisService->getOriginalClient()->del($key);
        }
    }

    public function exists($key)
    {
        return !!$this->_redisService->exists(self::CACHE_PREFIX . $key);
    }

    public function info()
    {
        return $this->_redisService->stats();
    }

    public function size()
    {
        return $this->info()[\Doctrine\Common\Cache\Cache::STATS_MEMORY_USAGE];
    }
}