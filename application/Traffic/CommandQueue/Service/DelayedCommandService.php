<?php
namespace Traffic\CommandQueue\Service;

use Traffic\CommandQueue\QueueStorage\StorageInterface;
use Traffic\Service\AbstractService;
use Traffic\Logging\Service\LoggerService;
use Traffic\Tools\Tools;

class DelayedCommandService extends AbstractService
{
    const PAYLOAD = 'payload';
    const COMMAND = 'command';
    const MAX_RETRIES = 3;
    const RETRY_PARAM = 'retry';
    const COMMAND_NAME_PARAM = 'command';

    private $_storage;
    private $_compression = false;

    public function setStorage(StorageInterface $storage)
    {
        $this->_storage = $storage;
    }

    /**
     * @return StorageInterface
     */
    private function getStorage()
    {
        return $this->_storage;
    }

    public function queueSize()
    {
        return $this->getStorage()->count();
    }

    public function clean()
    {
        LoggerService::instance()->debug("[DelayedCommandService] clean");
        return $this->getStorage()->clean();
    }

    public function enableCompression()
    {
        return $this->_compression = true;
    }

    public function push($command)
    {
        if (empty($this->_storage)) {
            throw new \Exception('Storage is not set');
        }
        LoggerService::instance()->debug(function () use ($command) {
            $count = $this->queueSize();
            return 'Pushed command: ' . json_encode($command) .' current count ' . $count;
        });


        $command = json_encode(Tools::utf8ize($command), JSON_PARTIAL_OUTPUT_ON_ERROR);

        $status = $this->getStorage()->push($command);

        LoggerService::instance()->debug(function () use ($command) {
            $count = $this->queueSize();
            return 'Pushed. Count ' . $count;
        });

        return $status;
    }

    public function popAll()
    {
        $result = [];
        foreach ($this->pop() as $item) {
            $result[] = $item;
        }
        return $result;
    }

    public function pop()
    {
        foreach ($this->getStorage()->pop() as $encodedData) {
            if (empty($encodedData)) {
                continue;
            }

            $data = json_decode($encodedData, true);

            if (json_last_error()) {
                LoggerService::instance()->warning('An issue while decoding string "' . $encodedData . '" (' . json_last_error_msg() . '). Skipped.');
                continue;
            }

            yield $data;
        }
    }

    public function count()
    {
        return $this->getStorage()->count();
    }

    public function isRetryAvailable(&$payload)
    {
        if (empty($payload[self::COMMAND_NAME_PARAM])) {
            return false;
        }
        $payload[self::RETRY_PARAM]++;
        if ($payload[self::RETRY_PARAM] >= self::MAX_RETRIES) {
            return false;
        }
        return true;
    }

    public function retry($payload)
    {
        if (!$this->isRetryAvailable($payload)) {
            return false;
        }
        $command = [
            self::PAYLOAD => $payload,
            self::COMMAND => $payload[self::COMMAND_NAME_PARAM],
        ];
        $this->push($command);
        return true;
    }

    public function initRetry(&$payload, $commandName)
    {
        $payload[self::COMMAND_NAME_PARAM] = $commandName;
        $payload[self::RETRY_PARAM] =  0;
    }
}
