<?php
namespace Traffic\Logging\Service;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Traffic\Service\AbstractService;
use Traffic\Service\ConfigService;
use Core\Logging\Service\LogParserService;

abstract class BaseLoggerService extends AbstractService
{
    /**
     * @var Logger
     */
    protected $_logger;
    protected $_jid;
    protected $_logDirectory;
    protected $_logFilename;
    protected $_contextName;

    const DATETIME_FORMAT = "Y-m-d H:i:s";

    abstract protected function _getLogLevel();
    abstract protected function _getDefaultLogFileName();
    abstract protected function _getLogFormat();
    abstract protected function _getLogFormatter();

    protected function _getLogFileName()
    {
        if (empty($this->_logFilename)) {
            return $this->_getDefaultLogFileName();
        } else {
            return $this->_logFilename;
        }
    }

    protected function _getLogDirectory()
    {
        if (empty($this->_logDirectory)) {
            return ROOT . "/var/log";
        } else {
            return $this->_logDirectory;
        }
    }

    protected function _getLogPath()
    {
        return $this->_getLogDirectory() . "/" . $this->_getLogFileName() . '.log';
    }

    protected function _getSystemLogFormatter()
    {
        $jid = $this->getJid();
        // the default date format is "Y-m-d H:i:s"
        $dateFormat = self::DATETIME_FORMAT;
        // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
        $output = "[%datetime%] [%level_name%] (jid:{$jid}) %context% : %message% \n";
        // finally, create a formatter
        return new LineFormatter($output, $dateFormat);
    }

    protected function _getSimpleLogFormatter()
    {
        $jid = $this->getJid();
        // the default date format is "Y-m-d H:i:s"
        $dateFormat = self::DATETIME_FORMAT;
        // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
        $output = "[%datetime%] (jid:{$jid}) : %message% \n";
        // finally, create a formatter
        return new LineFormatter($output, $dateFormat);
    }

    public function getJid()
    {
        if (!isset($this->_jid)) {
            $this->_jid = uniqid();
        }
        return $this->_jid;
    }

    public function setLogDirectory($logDirectory)
    {
        $this->_logDirectory = $logDirectory;
    }

    public function setLogFilename($filename)
    {
        $this->_logFilename = $filename;
        $stream = new StreamHandler($this->_getLogPath(), Logger::DEBUG);
        $stream->setFormatter($this->_getLogFormatter());
        $this->_logger->setHandlers([$stream]);
    }

    public function cleanLog()
    {
        LogParserService::instance()->cleanLog($this->_getLogDirectory(), $this->_getLogFileName());
    }

    public function getRows($offset, $limit, \DateTimeZone $tz = null, $query = null)
    {
        $entries = LogParserService::instance()->getRows($this->_getLogDirectory(), $this->_getLogFileName(), $offset, $limit, $this->_getLogFormat(), $query);
        if ($tz) {
            $entries = $this->_setTimezone($entries, $tz);
        }
        return $entries;
    }

    private function _setTimezone($entries, $tz)
    {
        foreach ($entries as $i => $entry) {
            $date = new \DateTime($entries[$i]['datetime']);
            $date->setTimezone($tz);
            $entries[$i]['datetime'] = $date->format(self::DATETIME_FORMAT);
        }
        return $entries;
    }

    public function getMaxSize()
    {
        return ConfigService::instance()->get(
            'system', 'log_max_size', 1000000
        );
    }

    public function checkSize()
    {
        $path = $this->_getLogDirectory();
        $list = LogParserService::instance()->getLogList($path, $this->_getLogFileName());
        rsort($list);
        $sizeSum = 0;
        foreach($list as $file) {
            $filename = $path . "/" . $file;
            $sizeSum += filesize($filename);
            if ($sizeSum > $this->getMaxSize()) {
                unlink($filename);
            }
        }
    }
}