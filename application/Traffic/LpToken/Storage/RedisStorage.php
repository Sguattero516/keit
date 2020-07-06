<?php
namespace Traffic\LpToken\Storage;

use Traffic\Redis\Service\RedisStorageService;

class RedisStorage implements StorageInterface
{
    const NAME = 'RW_CLCS';
    const COMPRESSION_LEVEL = 9;

    private $_compressionEnabled = false;
    private $_prefix;
    private $_redis;

    public function __construct($compression = false)
    {
        $this->_prefix = strtoupper(substr(md5(SALT), 10, 5));
        $this->_compressionEnabled = $compression;
        $this->_redis = RedisStorageService::instance();
    }

    public function enableCompression()
    {
        $this->_compressionEnabled = true;
    }

    public function set($token, $value, $ttl)
    {
        $key = $this->_buildKey($token);

        if ($this->_compressionEnabled) {
            $value = gzcompress($value, self::COMPRESSION_LEVEL);
        }

        $this->_redis->setEx($key, (int)$ttl, $value);
    }

    public function get($token)
    {
        $key = $this->_buildKey($token);

        $result = $this->_redis->get($key);

        if ($result !== false && $this->_compressionEnabled) {
            $result = gzuncompress($result);
        }

        return $result;
    }

    public function delete($token)
    {
        $this->_redis->delete($this->_buildKey($token));
    }

    private function _buildKey($token)
    {
        return self::NAME . ':' . $token;
    }
}