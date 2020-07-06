<?php
namespace Traffic\Logging\Service;

use Monolog\Logger;
use Core\Logging\Service\LogParserService;
use Traffic\Logging\NullTrafficLogEntry;
use Traffic\Logging\TrafficLogEntry;

class TrafficLoggerService extends LoggerService
{
    /**
     * @var TrafficLogEntry
     */
    private $_entry;

    public function __construct($dir = NULL)
    {
        parent::__construct($dir);
        $this->_entry = new NullTrafficLogEntry();
    }

    public function setEntry(TrafficLogEntry $entry)
    {
        $this->_entry = $entry;
    }

    public function flush()
    {
        $lines = $this->_entry->flush();
        if (count($lines)) {
            $this->info(implode("\t ", $lines));
        }
    }

    public function entry()
    {
        return $this->_entry;
    }

    protected function _getLogLevel()
    {
        return Logger::INFO;
    }

    protected function _getDefaultLogFileName()
    {
        return "traffic";
    }

    protected function _getLogFormat()
    {
        return LogParserService::TRAFFIC_FORMAT;
    }

    protected function _getLogFormatter()
    {
        return $this->_getSimpleLogFormatter();
    }
}