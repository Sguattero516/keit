<?php
namespace Traffic\Session\Storage;

use Core\Application\Application;
use Traffic\Model\BaseStream;
use Traffic\Model\Campaign;
use Traffic\RawClick;
use Traffic\Redis\Service\RedisStorageService;
use Traffic\Request\ServerRequest;
use Traffic\Response\Response;
use Traffic\Session\SessionEntry;

class RedisStorage implements StorageInterface
{
    const KEY = 'IP_SESSIONS';
    const COMPRESSION_LEVEL = 9;

    private $_memoized = array();
    private $_compressionEnabled = false;

    public function __construct(ServerRequest $serverRequest)
    {
        $this->_compressionEnabled = Application::instance()->isCompressionEnabled();
    }

    public function save(ServerRequest $serverRequest, Response $response, RawClick $visitor, Campaign $campaign, BaseStream $stream = NULL)
    {
        $sessionEntry = $this->_getSessionEntry($visitor->getVisitorCode());
        $sessionEntry->touch($visitor, $campaign, $stream);
        $this->_save($visitor->getVisitorCode(), $sessionEntry, $campaign->getCookiesTtl());

        return [$serverRequest, $response];
    }

    public function isUniqueForCampaign(RawClick $rawClick, Campaign $campaign)
    {
        $sessionEntry = $this->_getSessionEntry($rawClick->getVisitorCode());
        return $sessionEntry->isUniqueForCampaign($rawClick, $campaign);
    }

    public function isUniqueForStream(RawClick $rawClick, Campaign $campaign, BaseStream $stream)
    {
        $sessionEntry = $this->_getSessionEntry($rawClick->getVisitorCode());
        return $sessionEntry->isUniqueForStream($rawClick, $campaign, $stream);
    }

    public function isUniqueGlobal(RawClick $rawClick, Campaign $campaign)
    {
        $sessionEntry = $this->_getSessionEntry($rawClick->getVisitorCode());
        return $sessionEntry->isUniqueGlobal($rawClick, $campaign);
    }

    public function flushMemo()
    {
        $this->_memoized = [];
    }

    /**
     * @param $visitorCode string
     * @return SessionEntry
     */
    private function _getSessionEntry($visitorCode)
    {
        $data = RedisStorageService::instance()->get($this->_getKey($visitorCode));
        if (empty($data)) {
            return SessionEntry::restore([]);
        }

        if ($this->_compressionEnabled) {
            $firstByte = mb_substr($data, 0, 1);
            if ($firstByte !== '"' && $firstByte !== '{') {
                $data = gzuncompress($data);
            }
        }

        $decodedData = json_decode($data, true);
        return SessionEntry::restore($decodedData);
    }

    private function _getKey($visitorCode)
    {
        return self::KEY . ':' . $visitorCode;
    }

    private function _save($visitorCode, SessionEntry $sessionEntry, $ttl)
    {
        $redisKey = $this->_getKey($visitorCode);

        $encodedData = json_encode($sessionEntry->getData());

        if ($this->_compressionEnabled) {
            $encodedData = gzcompress($encodedData, self::COMPRESSION_LEVEL);
        }

        RedisStorageService::instance()->setEx($redisKey, $ttl * 60 * 60, $encodedData);
    }
}