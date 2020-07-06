<?php
namespace Traffic\Redis\Service;

use Core\Application\Application;
use Core\Application\Exception\Error;
use Doctrine\Common\Cache\Cache;
use Redis;
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Service\ConfigService;
use Traffic\Logging\Service\LoggerService;
use Traffic\Service\AbstractService;
use Traffic\Service\SettingsService;

/**
 * @method static Redis|RedisStorageService instance()
 */
class RedisStorageService extends AbstractService
{
    const DRAFT_DATA_STORAGE = 'draft_data_storage';

    const REDIS = 'redis';

    private $_forceDisabled = false;

    /**
     * @var Redis
     */
    private $_redis;

    private $_initialized = false;

    public function init()
    {
        if ($this->_initialized) {
            return;
        }

        $this->_redis = $this->newRedisInstance();

        $this->_initialized = true;
    }

    public function newRedisInstance()
    {
        $uri = $this->_getRedisUri();

        if (!class_exists('Redis')) {
            throw new Error('Extension php_redis not installed!');
        }

        $redis = new Redis();
        $url = parse_url($uri);
        if (!isset($url['host']) || !isset($url['port'])) {
            throw new Error('Redis URL incorrect');
        }
        try {
            $redis->pconnect($url['host'], $url['port']);
            if (!empty($url['user'])) {
                $inPass = $url['user'];
                $passToTry = [
                    $inPass,
                    hash('sha256', $inPass),
                    hash('sha256', $inPass . "\n"),
                ];
                foreach ($passToTry as $pass) {
                    $res = $redis->auth($pass);
                    if ($res) break;
                }
            }

            if (!empty($url['path'])) {
                $redis->select(str_replace('/', '', $url['path']));
            }

        } catch (\RedisException $e) {
            $this->_forceDisabled = true;
            LoggerService::instance()->error('Error while connecting redis: ' . $e->getMessage());
        }

        return $redis;
    }

    private function _getRedisUri()
    {
        $uri = ConfigService::instance()->get('redis', 'uri');

        if (empty($uri)) {
            $uri = CachedSettingsRepository::instance()->get('redis_server');
        }
        return $uri;
    }

    /**
     * @return Redis
     */
    public function getOriginalClient()
    {
        $this->init();
        return $this->_redis;
    }

    public function clean($force = false)
    {
        LoggerService::instance()->debug("[RediStorageService] clean");

        if (!$this->isEnabled() && !$force) {
            return null;
        }
        $this->init();
        return $this->getOriginalClient()->flushDB();
    }

    public function prefix()
    {
        $prefix = strtoupper(substr(md5(SALT), 10, 5)) . '::';
        $prefix .= strtoupper(Application::instance()->getEnv()) . '::';

        return $prefix;
    }

    public function disable()
    {
        $this->_forceDisabled = true;
    }

    public function enable()
    {
        $this->_forceDisabled = false;
    }

    public function redisServerExists()
    {
        return class_exists('Redis');
    }

    public function draftStorageUsesRedis()
    {
        return $this->isEnabled() && CachedSettingsRepository::instance()->get(self::DRAFT_DATA_STORAGE) == self::REDIS;
    }

    public function isEnabled()
    {
        if ($this->_forceDisabled) {
            return false;
        }

        if (!$this->redisServerExists()) {
            return false;
        }

        if (!class_exists('Redis')) {
            return false;
        }

        return true;
    }

    public function isRedisAlive()
    {
        try {
            $pingReply = $this->getOriginalClient()->ping();
            if ($pingReply !== '+PONG' && $pingReply != '1') {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function __call($method, $args)
    {
        $this->init();

        if (isset($args[0]) && is_string($args[0])) {
            $args[0] = $this->prefix() . $args[0];
        }

        if (!method_exists($this->getOriginalClient(), $method)) {
            throw new Error('No method Redis#' . $method);
        }

        try {
            return call_user_func_array(array($this->getOriginalClient(), $method), $args);
        } catch (Error $e) {
            LoggerService::instance()->error(
                'Error while send command to redis: ' . $e->getMessage() . '. Saving data to DB.'
            );
            $this->_forceDisabled = true;
            return null;
        }
    }

    public function stats()
    {
        $info = $this->getOriginalClient()->info();
        return array(
            Cache::STATS_HITS   => $info['keyspace_hits'],
            Cache::STATS_MISSES => $info['keyspace_misses'],
            Cache::STATS_UPTIME => $info['uptime_in_seconds'],
            Cache::STATS_MEMORY_USAGE      => $info['used_memory'],
            Cache::STATS_MEMORY_AVAILABLE  => false
        );
    }
}