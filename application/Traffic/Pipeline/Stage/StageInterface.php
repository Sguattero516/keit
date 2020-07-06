<?php
namespace Traffic\Pipeline\Stage;

use Traffic\Logging\TrafficLogEntry;
use Traffic\Pipeline\Payload;

interface StageInterface
{
    /**
     * @param Payload $payload
     * @param TrafficLogEntry $logEntry
     * @return mixed
     */
    public function process(Payload $payload, TrafficLogEntry $logEntry);
}