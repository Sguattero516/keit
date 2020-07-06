<?php
namespace Traffic\Logging;

class NullTrafficLogEntry extends TrafficLogEntry
{
    public function add($msg) {}
    public function addLazy($msgLazy) {}
}