<?php
namespace Traffic\HitLimit\Storage;

use Component\StreamFilters\Repository\StreamFilterRepository;
use Core\Db\Db;
use Traffic\Model\BaseStream;
use Traffic\Redis\Service\RedisStorageService;

class RedisStorage implements StorageInterface
{
    const SET_PREFIX = 'rate:';
    const FILTER_NAME = 'limit';
    const COLLECTION_SET = 'rate_collection';
    const TTL = 1;

    public function store(BaseStream $stream, \DateTime $currentDateTime)
    {
        RedisStorageService::instance()->zadd($this->_getSetName($stream->getId()), $currentDateTime->getTimestamp(), $this->_getRand());
        RedisStorageService::instance()->sAdd(self::COLLECTION_SET, $stream->getId());
    }

    public function perHour(BaseStream $stream, \DateTime $currentDateTime)
    {
        $currentDateTime = clone($currentDateTime);
        $from = $currentDateTime->modify('-1 hour')->getTimestamp();
        return RedisStorageService::instance()->zcount($this->_getSetName($stream->getId()), $from, '+inf');
    }

    public function perDay(BaseStream $stream, \DateTime $date)
    {
        $date = clone($date);
        $from = $date->modify('-1 day')->getTimestamp();
        return RedisStorageService::instance()->zcount($this->_getSetName($stream->getId()), $from, '+inf');
    }

    public function total(BaseStream $stream)
    {
        return RedisStorageService::instance()->zcount($this->_getSetName($stream->getId()), '-inf', '+inf');
    }

    private function _getSetName($streamId)
    {
        return self::SET_PREFIX . $streamId;
    }

    private function _getRand()
    {
        return date('YmdHis') . rand(10000, 999999);
    }

    public function prune(\DateTime $currentDateTime)
    {
        $exceptions = $this->_getStreamIdsWithLimitTotal();

        $zNames = RedisStorageService::instance()->keys(self::SET_PREFIX . '*');
        $time = clone $currentDateTime;
        $until = $time->modify('-' . self::TTL . ' day')->getTimestamp();

        if (is_array($zNames)) {
            foreach ($zNames as $name) {
                $streamId = str_replace(self::SET_PREFIX, '', $name);
                $streamId = str_replace(RedisStorageService::instance()->prefix(), '', $streamId);

                if (!in_array($streamId, $exceptions)) {
                    RedisStorageService::instance()->getOriginalClient()->zRemRangeByScore($name, '-inf', $until);
                }
            }
        }
    }

    private function _getStreamIdsWithLimitTotal()
    {
        $where = 'name = ' . Db::quote(self::FILTER_NAME);
        $ids = array();
        foreach (StreamFilterRepository::instance()->all($where) as $filter) {
            $payload = $filter->getPayload();
            if (!empty($payload['total'])) {
                $ids[] = $filter->getStreamId();
            }
        }
        return $ids;
    }
}