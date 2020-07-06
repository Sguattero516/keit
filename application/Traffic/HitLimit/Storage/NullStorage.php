<?php
namespace Traffic\HitLimit\Storage;

use Traffic\Model\BaseStream;

class NullStorage implements StorageInterface
{
    public function store(BaseStream $stream, \DateTime $currentDateTime)
    {
        // noop
    }

    public function perHour(BaseStream $stream, \DateTime $currentDateTime)
    {
        return 0;
    }

    public function perDay(BaseStream $stream, \DateTime $date)
    {
        return 0;
    }

    public function total(BaseStream $stream)
    {
        return 0;
    }

    public function prune(\DateTime $currentDateTime)
    {
        // noop
    }
}
