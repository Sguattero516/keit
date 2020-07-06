<?php
namespace Traffic\Logging\Service;

use Core\Application\Application;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Traffic\Service\ConfigService;
use Core\Logging\Service\LogParserService;

class LoggerService extends BaseLoggerService
{
    const WARNING = 'warning';
    const DEBUG = 'debug';

    protected $_contextName = 'unknown';
    protected $_stopWords = [
        'unlink',
        'rmdir',
        'chmod',
        'No such file or directory',
        'is not within the allowed path(s): (C:/Inetpub', // IIS on Windows has allowed path list
        'already loaded',
        'disk_free_space',
        'is_readable()'
    ];

    protected $_handler;

    public function getLevel()
    {
        $level = ConfigService::instance()->get('system', "log_level", self::WARNING);
        if ($level == 'warn') {
            $level = self::WARNING;
        }
        return $level;
    }

    protected function _getLogLevel()
    {
        return constant('Monolog\Logger::' . strtoupper($this->getLevel()));
    }

    protected function _getLogFormat()
    {
        return LogParserService::SYSTEM_FORMAT;
    }

    protected function _getDefaultLogFileName()
    {
        return Application::instance()->getEnv();
    }

    protected function _getLogFormatter()
    {
        return $this->_getSystemLogFormatter();
    }

    public function setContextName($name)
    {
        $this->_contextName = $name;
    }

    // TODO перенести в Bootstrap
    public function __construct($dir = null)
    {
        if (!empty($dir)) {
            $this->setLogDirectory($dir);
        }

        $this->_logger = new Logger('main');

        $formatter = $this->_getLogFormatter();

        if (Application::instance()->isProduction()) {
            $this->_handler = new RotatingFileHandler($this->_getLogPath(), 4, $this->_getLogLevel());
        } else {
            $this->_handler = new StreamHandler($this->_getLogPath(), $this->_getLogLevel());
        }

        $this->_handler->setFormatter($formatter);

        if (!Application::instance()->isRoadRunnerRunning()
            && !Application::instance()->isTesting()
            && !Application::instance()->isDevelopment()
            && !defined('CRON')
        ) {
            $this->_handler = new BufferHandler($this->_handler, 10000, $this->_getLogLevel(), true, true);
        }

        $this->_logger->pushHandler($this->_handler);
    }

    public function flush()
    {
        if ($this->_handler instanceof BufferHandler) {
            $this->_handler->flush();
        }
    }

    public function closeHandler()
    {
        $this->_handler->close();
    }

    public function info($msg)
    {
        $this->log(Logger::INFO, $msg);
    }

    public function debug($msg)
    {
        $this->log(Logger::DEBUG, $msg);
    }

    public function warning($msg)
    {
        $this->log(Logger::WARNING, $msg);
    }

    public function error($msg)
    {
        $this->log(Logger::ERROR, $msg);
    }

    /**
     * @param $level string
     * @param $msg string|callable
     */
    public function log($level, $msg)
    {
        if ($level == Logger::DEBUG && $this->getLevel() != self::DEBUG) { // that one for performance reasons
            return;
        }
        $this->_logger->log($level, $this->_unpackMessage($msg), [$this->_contextName]);
    }

    public function setErrorHandlers()
    {
        register_shutdown_function(array($this, 'shutdownHandler'));
        set_error_handler(array($this, 'errorHandler'));
    }

    public function shutdownHandler() //will be called when php script ends.
    {
        $lastError = error_get_last();

        switch ($lastError['type']) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_PARSE:
                $error = $this->formatPHPError(
                    $lastError['type'],
                    $lastError['message'],
                    $lastError['file'],
                    $lastError['line']
                );
                $this->error($error);
        }
    }

    protected function getErrorName($code)
    {
        $error = "";
        switch ($code) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $error = 'Fatal Error';
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                $error = 'Warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $error = 'Notice';
                break;
            case E_STRICT:
                $error = 'Strict';
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $error = 'Deprecated';
                break;
            default:
                break;
        }
        return $error;
    }

    protected function formatPHPError($error_level, $error_message, $error_file, $error_line)
    {
        return $this->getErrorName($error_level) . " in ".$error_file." line ".$error_line.": ".$error_message;
    }

    public function errorHandler($error_level, $error_message, $error_file, $error_line)
    {
        if ($this->_shouldFilter($error_message)) {
            return;
        }
        $error = $this->formatPHPError($error_level, $error_message, $error_file, $error_line);

        switch ($error_level) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_PARSE:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                $this->error($error);
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                $this->log(Logger::WARNING, $error);
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $this->log(Logger::NOTICE, $error);
                break;
            case E_STRICT:
                $this->log(Logger::DEBUG, $error);
                break;
            default:
                $this->log(Logger::WARNING, $error);
        }
    }

    protected function _shouldFilter($message)
    {
        foreach ($this->_stopWords as $word) {
            if (strpos($message, $word) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $msg string|callable
     * @return string
     */
    private function _unpackMessage($msg)
    {
        if (is_callable($msg)) {
            return $msg();
        }
        return $msg;
    }
}
