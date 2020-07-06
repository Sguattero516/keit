<?php
namespace Traffic\CachedData\Storage;

use Traffic\Cache\Cache;

class Factory
{
    public static function build($cacheName, $compressionEnabled)
    {
        switch ($cacheName) {
            case Cache::STORAGE_REDIS:
                $storage = new RedisStorage();
                if ($compressionEnabled) {
                    $storage->enableCompression();
                }
                return $storage;
            case Cache::STORAGE_OFF:
                return new NullStorage();
            default:
                return new FileStorage();
        }
    }
}