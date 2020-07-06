<?php
namespace Traffic\Session;

use Traffic\Model\Campaign;
use Traffic\Model\BaseStream;

use Traffic\RawClick;

class SessionEntry
{
    const STREAMS = 'streams';
    const CAMPAIGNS = 'campaigns';

    const EXPIRE_DAYS = 2;
    private $_data = array();

    public function __construct($data)
    {
        if (is_array($data)) {
            $this->_data = $data;
        }
    }

    public function getId()
    {
        return isset($this->_data['id']) ? $this->_data['id'] : null;
    }

    public function getData()
    {
        return $this->_data;
    }

    public static function restore($data)
    {
        $obj = new SessionEntry($data);
        return $obj->outdated();
    }

    public function outdated()
    {
        $expire = time() - self::EXPIRE_DAYS * 24 * 3600;
        if (isset($this->_data[self::STREAMS])) {
            $this->_data[self::STREAMS] = self::checkArray($this->_data[self::STREAMS], $expire);
        }
        if (isset($this->_data[self::CAMPAIGNS])) {
            $this->_data[self::CAMPAIGNS] = self::checkArray($this->_data[self::CAMPAIGNS], $expire);
        }
        return $this;
    }

    public static function checkArray($arr, $timestamp)
    {
        if (is_array($arr) && count($arr) > 0) {
            foreach($arr AS $id => $time) {
                if ($time < $timestamp) {
                    unset($arr[$id]);
                }
            }
        }
        return $arr;
    }

    public function touch(RawClick $rawClick, Campaign $campaign, BaseStream $stream = NULL)
    {
        $datetime = $rawClick->getDateTime();
        
        if (!empty($stream)) {
            $currentTime = empty($this->_data[self::STREAMS][$stream->getId()]) ? 0 : $this->_data[self::STREAMS][$stream->getId()];
            if (!$currentTime || $datetime->getTimestamp() - $currentTime >= $campaign->getCookiesTtl() * 60 * 60) {
                $this->_data[self::STREAMS][$stream->getId()] = $datetime->getTimestamp();
            }
        }

        $currentTime = empty($this->_data[self::CAMPAIGNS][$campaign->getId()]) ? 0 : $this->_data[self::CAMPAIGNS][$campaign->getId()];
        if (!$currentTime || $datetime->getTimestamp() - $currentTime >= $campaign->getCookiesTtl() * 60 * 60) {
            $this->_data[self::CAMPAIGNS][$campaign->getId()] = $datetime->getTimestamp();
        }

        $currentTime = empty($this->_data['time']) ? 0 : $this->_data['time'];
        if (!$currentTime || $datetime->getTimestamp() - $currentTime >= $campaign->getCookiesTtl() * 60 * 60) {
            $this->_data['time'] = $datetime->getTimestamp();
        }
        return $this;
    }

    public function isUniqueForCampaign(RawClick $rawClick, Campaign $campaign)
    {
        if (!isset($this->_data[self::CAMPAIGNS][$campaign->getId()])) {
            return true;
        }
        $value = $this->_data[self::CAMPAIGNS][$campaign->getId()];

        return !$this->_isActive($value, $rawClick->getDateTime(), $campaign->getCookiesTtl());
    }

    public function isUniqueForStream(RawClick $visitor, Campaign $campaign, BaseStream $stream)
    {
        if (!isset($this->_data[self::STREAMS][$stream->getId()])) {
            return true;
        }

        $value = $this->_data[self::STREAMS][$stream->getId()];

        return !$this->_isActive($value, $visitor->getDateTime(), $campaign->getCookiesTtl());
    }

    public function isUniqueGlobal(RawClick $rawClick, Campaign $campaign)
    {
        if (!isset($this->_data[self::CAMPAIGNS])) {
            return true;
        }

        if (isset($this->_data['time'])) {
            $value = $this->_data['time'];
        } else {
            return true;
        }

        if (!($rawClick->getDateTime() instanceof \DateTime)) {
            throw new \Exception('RawClick#date_time must be DateTime');
        }

        return !$this->_isActive($value, $rawClick->getDateTime(), $campaign->getCookiesTtl());
    }

    private function _isActive($value, \DateTime $datetime, $expireHours)
    {
        return ($value + $expireHours * 60 * 60) > $datetime->getTimestamp();
    }
}