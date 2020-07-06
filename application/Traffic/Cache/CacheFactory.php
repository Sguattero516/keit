<?php
namespace Traffic\Cache;

class CacheFactory
{
    const COMMON = 'common';

    public static function build($name, $storage, $opts = [])
    {
        if (empty($storage)) {
            $storage = Cache::STORAGE_OFF;
        }

        if (!in_array($storage, static::getStorages())) {
            if ($storage != Cache::STORAGE_ARRAY) {
                $storage = static::_getAnyStorage();
            }
        }

        if (!CacheFactory::isAllowed($storage)) {
            throw new CacheError("Storage '{$storage}' is not available");
        }

        return new Cache($name, $storage, $opts);
    }

    public static function getAvailableStorages()
    {
        $options = array();
        $options[] = array('value' => 'auto', 'name' => 'Auto');
        foreach (static::getStorages() as $storage) {
            if (static::isAllowed($storage)) {
                $options[] = array(
                    'value' => $storage,
                    'name' => ucfirst($storage)
                );
            }
        }
        return $options;
    }

    public static function getStorages()
    {
        return array(
            Cache::STORAGE_FILES,
            Cache::STORAGE_REDIS,
        );
    }

    public static function isAllowed($storage)
    {
        switch ($storage) {
            case Cache::STORAGE_APC:
                return (extension_loaded('apc') && ini_get('apc.enabled'));

            case Cache::STORAGE_MEMCACHE:
                return function_exists("memcache_connect");

            case Cache::STORAGE_MEMCACHED:
                return class_exists('Memcached');

            case Cache::STORAGE_XCACHE:
                return (extension_loaded('xcache') && function_exists('xcache_get'));

            case Cache::STORAGE_SQLITE:
                return extension_loaded('pdo_sqlite');

            case Cache::STORAGE_REDIS:
                return extension_loaded('redis');
            case Cache::STORAGE_FILES:
            case Cache::STORAGE_ARRAY:
            case Cache::STORAGE_OFF:
                return true;
        }

        return false;
    }

    protected static function _getAnyStorage()
    {
        $variations = array(
            Cache::STORAGE_FILES, Cache::STORAGE_OFF
        );

        foreach ($variations as $tryStorage) {
            if (static::isAllowed($tryStorage)) {
                return $tryStorage;
            }
        }
        return Cache::STORAGE_FILES;
    }
}
