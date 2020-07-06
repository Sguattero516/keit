<?php
namespace Traffic\LpToken\Storage;

use Traffic\Redis\Service\RedisStorageService;

class Factory
{
    /**
     * @param $compression
     * @return StorageInterface]
     */
    public static function build($compression)
    {
        if (RedisStorageService::instance()->draftStorageUsesRedis()) {
            $storage = new RedisStorage();
            if ($compression) {
                $storage->enableCompression();
            }
            return $storage;
        }

        return new DoctrineFileStorage();
    }
}