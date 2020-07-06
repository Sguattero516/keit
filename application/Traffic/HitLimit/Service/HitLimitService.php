<?php
namespace Traffic\HitLimit\Service;

use Traffic\HitLimit\Storage\StorageInterface;
use Traffic\Model\BaseStream;
use Traffic\Service\AbstractService;

class HitLimitService extends AbstractService
{
    /**
     * @var StorageInterface
     */
    private $_storage;

    public function setStorage(StorageInterface $storage)
    {
        return $this->_storage = $storage;
    }

    public function store(BaseStream $stream, \DateTime $date)
    {
        return $this->_storage->store($stream, $date);
    }

    public function perHour(BaseStream $stream, \DateTime $date)
    {
        return $this->_storage->perHour($stream, $date);
    }

    public function perDay(BaseStream $stream, \DateTime $date)
    {
        return $this->_storage->perDay($stream, $date);
    }

    public function total(BaseStream $stream)
    {
        return $this->_storage->total($stream);
    }

    public function prune(\DateTime $date)
    {
        return $this->_storage->prune($date);
    }

    public function getState(BaseStream $stream, \DateTime $date)
    {
        return [
            'per_hour' => $this->perDay($stream, $date),
            'per_day' => $this->perDay($stream, $date),
            'total' => $this->total($stream)
        ];
    }
}