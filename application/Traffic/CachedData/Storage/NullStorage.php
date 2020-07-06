<?php
namespace Traffic\CachedData\Storage;

use Traffic\Cache\NoCache;
use Traffic\Logging\Service\LoggerService;

class NullStorage implements StorageInterface
{
    public function enableCompression()
    {
    }

    public function set($key, $data)
    {
        LoggerService::instance()->debug('[NullStorage] skip save');
    }

    public function commit() {}

    public function get($key)
    {
        throw new NoCache("no key '{$key}'");
    }

    public function delete($key) {}

    public function deleteAll() {}

    public function exists($key)
    {
        return false;
    }

    public function info()
    {
        return [\Doctrine\Common\Cache\Cache::STATS_MEMORY_USAGE => 0];
    }

    public function size()
    {
    }
}