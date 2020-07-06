<?php

namespace Traffic\Cache;

use Core\FileSystem\Service\FileSystemService;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Traffic\Service\AbstractService;

class FlushOldNamespacedCacheService extends AbstractService
{
    /**
     * @var CacheProvider
     */
    private $_doctrineCache;

    /**
     * @var string
     */
    private $_namespaceHash;

    public function init(CacheProvider $doctrineCache, $namespaceHash)
    {
        $this->_doctrineCache = $doctrineCache;
        $this->_namespaceHash = $namespaceHash;
    }

    public function flush()
    {
        if (is_null($this->_doctrineCache) || is_null($this->_namespaceHash)) {
            throw new Exception('Call init() method first');
        }

        switch (get_class($this->_doctrineCache)) {
            case FilesystemCache::class:
                return $this->_flushOldNamespacedFilesystemCache();
            case RedisCache::class:
                return $this->_flushOldNamespacedRedisCache();
            default:
                return false;
        }
    }

    private function _flushOldNamespacedFilesystemCache()
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->_doctrineCache->getDirectory(),
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $namespaceCacheKeyStart = substr(CacheProvider::DOCTRINE_NAMESPACE_CACHEKEY, 0, -4);
        $namespaceCacheKeyStartEncoded = bin2hex(substr(CacheProvider::DOCTRINE_NAMESPACE_CACHEKEY, 0, -4));

        $namespacesVersions = [];
        $filenamesByNamespaceVersions = [];
        $fsService = FileSystemService::instance();
        foreach ($iterator as $name => $file) {
            if ($file->isDir() || strpos($name, FilesystemCache::EXTENSION) === false || strpos($name, '_') === 0) {
                continue;
            }
            if (strstr($name, 'unison')) {
                continue;
            }

            $cacheFileBasename = basename(substr($name, 0, strpos($name, FilesystemCache::EXTENSION)));
            $decodedFilename = hex2bin($cacheFileBasename);

            if ($decodedFilename === false) {
                continue;
            }

            if (strpos($cacheFileBasename, $namespaceCacheKeyStartEncoded) === 0) {
                if (!preg_match(
                    '/' . $namespaceCacheKeyStart . '\[(?<namespace>\w+)\]/',
                    $decodedFilename,
                    $matches
                )) {
                    continue;
                }

                $namespace = $matches['namespace'];
                if (!$this->_isCacheOfCurrentTracker($namespace)) {
                    continue;
                }

                $namespaceVersion = file_get_contents($name);
                $namespaceVersion = unserialize(explode("\n", $namespaceVersion)[1]);

                $namespacesVersions[$namespace] = $namespaceVersion;
            } elseif (preg_match('/(?<namespace>\w+)\[.*\[(?<version>\d+)\]/', $decodedFilename, $filenameMatches)) {
                $filenamesByNamespaceVersions[$filenameMatches['namespace']][$filenameMatches['version']][] = $name;
            }
        }

        foreach ($namespacesVersions as $namespace => $version) {
            if (!isset($filenamesByNamespaceVersions[$namespace])) {
                continue;
            }

            foreach ($filenamesByNamespaceVersions[$namespace] as $filesVersion => $files) {
                if ($filesVersion < $version - 1) {
                    foreach ($files as $file) {
                        $fsService->remove($file);
                    }
                }
            }
        }

        return true;
    }

    private function _flushOldNamespacedRedisCache()
    {
        if (get_class($this->_doctrineCache) !== RedisCache::class) {
            return false;
        }

        $namespaceCacheKeyStart = substr(CacheProvider::DOCTRINE_NAMESPACE_CACHEKEY, 0, -4);
        $namespacesVersions = [];
        $redis = $this->_doctrineCache->getRedis();
        $iterator = null;
        while (($keys = $redis->scan($iterator, $namespaceCacheKeyStart . '*')) !== false) {
            foreach ($keys as $key) {
                if (!preg_match('/' . $namespaceCacheKeyStart . '\[(?<namespace>\w+)\]/', $key, $matches)) {
                    continue;
                }

                $namespace = $matches['namespace'];
                if (!$this->_isCacheOfCurrentTracker($namespace)) {
                    continue;
                }

                $namespacesVersions[$namespace] = $redis->get($key);
            }
        }

        foreach ($namespacesVersions as $namespace => $version) {
            if ($version <= 2) {
                continue;
            }

            $lowerVersions = array_reverse(range(1, $version - 2));
            foreach ($lowerVersions as $lowerVersion) {
                $iterator = null;
                $matchPattern = $namespace.'\[*\]\['.$lowerVersion.'\]';
                while (($keys = $redis->scan($iterator, $matchPattern)) !== false) {
                    $redis->del($keys);
                }
            }
        }

        return true;
    }

    private function _isCacheOfCurrentTracker($namespace)
    {
        return strpos($namespace, $this->_namespaceHash) !== false;
    }
}
