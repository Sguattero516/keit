<?php
namespace Traffic\LpToken\Storage;

use Traffic\Cache\CacheService;
use Traffic\Cache\NoCache;

class DoctrineFileStorage implements StorageInterface
{
    public function enableCompression()
    {
        // not implemented
    }

    public function set($token, $value, $ttl)
    {
        CacheService::instance()->lpOfferCache()->set($token, $value, $ttl);
    }

    public function get($token)
    {
        try {
            return CacheService::instance()->lpOfferCache()->get($token);
        } catch (NoCache $e) {
            return null;
        }
    }

    public function delete($token)
    {
        CacheService::instance()->lpOfferCache()->delete($token);
    }
}