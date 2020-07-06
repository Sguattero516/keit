<?php
namespace Traffic\Session\Storage;

use Core\Db\Db;
use Core\Model\AbstractModel;
use Traffic\Request\ServerRequest;
use Traffic\Response\Response;
use Traffic\Session\Session;
use Traffic\Model\BaseStream;
use Traffic\Model\Campaign;
use Traffic\RawClick;
use Traffic\Logging\Service\LoggerService;
use Traffic\Session\SessionEntry;

class MysqlStorage implements StorageInterface
{
    private $_memoized = [];

    const TABLE_NAME = 'sessions';

    public function __construct(ServerRequest $serverRequest)
    {
    }

    public function save(ServerRequest $serverRequest, Response $response, RawClick $visitor, Campaign $campaign, BaseStream $stream = NULL)
    {
        $entry = $this->_findSessionEntry($visitor->getVisitorCode());
        $entry->touch($visitor, $campaign, $stream);

        $ttl = $campaign->getCookiesTtl();
        $this->_save($visitor->getVisitorCode(), $entry, $ttl);
        return [$serverRequest, $response];
    }

    public function isUniqueForCampaign(RawClick $rawClick, Campaign $campaign)
    {
        $entry = $this->_findSessionEntry($rawClick->getVisitorCode());
        return $entry->isUniqueForCampaign($rawClick, $campaign);
    }

    public function isUniqueForStream(RawClick $rawClick, Campaign $campaign, BaseStream $stream)
    {
        $entry = $this->_findSessionEntry($rawClick->getVisitorCode());
        return $entry->isUniqueForStream($rawClick, $campaign, $stream);
    }

    public function isUniqueGlobal(RawClick $rawClick, Campaign $campaign)
    {
        $entry = $this->_findSessionEntry($rawClick->getVisitorCode());
        return $entry->isUniqueGlobal($rawClick, $campaign);
    }

    /**
     * @param $visitorCode string
     * @return SessionEntry
     */
    private function _findSessionEntry($visitorCode)
    {
        if (empty($this->_memoized[$visitorCode])) {
            $data = $this->_load($visitorCode);
            $sessionEntry = SessionEntry::restore($data);
            $this->_memoized[$visitorCode] = $sessionEntry;
        }
        return $this->_memoized[$visitorCode];
    }

    public function flushMemo()
    {
        $this->_memoized = [];
    }

    private function _load($visitorCode)
    {
        $sql = "SELECT * FROM {$this->_getTableName()} WHERE visitor_code = " . Db::quote($visitorCode);
        try {
            $row = Db::instance()->getRow($sql);
        } catch (\ADODB_Exception $e) {
            LoggerService::instance()->error('DB is down. Skip loading uniqueness state');
        }
        if (!empty($row)) {
            return json_decode($row['payload'], true);
        }
        return null;
    }

    private function _save($visitorCode, SessionEntry $entry, $ttl)
    {
        $payload = json_encode($entry->getData());
        $date = new \DateTime('+' . $ttl . 'hours');
        $expiresAt = $date->format(AbstractModel::DATETIME_FORMAT);

        try {
            Db::instance()->insert(Session::getTableName(), [
                'visitor_code' => $visitorCode,
                'expires_at' => $expiresAt,
                'payload' => $payload
            ], "ON DUPLICATE KEY UPDATE expires_at = " . Db::quote($expiresAt) . ", payload = " . Db::quote($payload));
        } catch (\ADODB_Exception $e) {
            LoggerService::instance()->error('Error connecting to MySQL. Skip saving uniqueness state. Message: ' . $e->getMessage());
        }
    }

    public function prune()
    {
        $date = new \DateTime();
        $sql = "DELETE FROM {$this->_getTableName()} 
          WHERE expires_at < " . Db::quote($date->format(AbstractModel::DATETIME_FORMAT));
        Db::instance()->execute($sql);
    }

    private function _getTableName()
    {
        return Db::getPrefix() . self::TABLE_NAME;
    }
}