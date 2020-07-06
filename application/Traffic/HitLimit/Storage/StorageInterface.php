<?php
namespace Traffic\HitLimit\Storage;

use Traffic\Model\BaseStream;

interface StorageInterface
{
    public function store(BaseStream $stream, \DateTime $currentDateTime);
    public function perHour(BaseStream $stream, \DateTime $currentDateTime);
    public function perDay(BaseStream $stream, \DateTime $date);
    public function total(BaseStream $stream);
    public function prune(\DateTime $currentDateTime);
}