<?php
namespace Traffic\CommandQueue\QueueStorage;

use Component\DelayedCommands\Processor\CommandAggregator;
use Traffic\Service\ConfigService;
use Traffic\Redis\Service\RedisStorageService;

class RedisStorage implements StorageInterface
{
    const QUEUE = 'COMMAND_QUEUE';

    const ADDITIONAL_REDIS_QUEUE = 'additional_redis_queue';

    const COMPRESSION_LEVEL = 9;

    const RANGE_SIZE = 1000;

    private $_queueName;

    private $_compressionEnabled;

    private $_redisService;

    private $_rangeSize;

    public function __construct($compression = false)
    {
        $this->setQueueName(self::QUEUE);
        $this->_compressionEnabled = $compression;
        $this->_redisService = RedisStorageService::instance();
    }

    public function enableCompression()
    {
        $this->_compressionEnabled = true;
    }

    public function isAvailable()
    {
        return $this->_redisService->isEnabled();
    }

    public function setQueueName($name)
    {
        $this->_queueName = $name;
    }

    public function push($data)
    {
        if ($this->_compressionEnabled) {
            $data = gzcompress($data, self::COMPRESSION_LEVEL);
        }

        $this->_redisService->rPush($this->getQueueName(), $data);

        if ($queueName = ConfigService::instance()->get('system', self::ADDITIONAL_REDIS_QUEUE)) {
            $this->_redisService->getOriginalClient()->rPush($queueName, $data);
        }
        return $this;
    }

    public function count()
    {
        return $this->_redisService->lLen($this->getQueueName());
    }

    public function clean()
    {
        $this->_redisService->del($this->getQueueName());
    }

    public function getQueueName()
    {
        return $this->_queueName;
    }

    public function pop()
    {
        $size = self::RANGE_SIZE;
        $key = $this->_redisService->prefix() . $this->getQueueName();

        while (true) {
            $pipe = $this->_redisService->multi(\Redis::PIPELINE);
            $pipe->lRange($key, 0, $size - 1);
            $pipe->lTrim($key, $size, -1);

            $result = $pipe->exec()[0];

            if (empty($result) || $result === false) {
                break;
            }
            foreach ($result as $item) {
                yield $this->_decode($item);
            }
        }
    }

    private function _decode($data)
    {
        if (empty($data)) {
            return null;
        }

        if ($this->_compressionEnabled) {
            $firstByte = mb_substr($data, 0, 1);
            if ($firstByte !== '"' && $firstByte !== '{') {
                $data = gzuncompress($data);
            }
        }
        return $data;
    }
}