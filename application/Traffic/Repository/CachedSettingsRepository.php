<?php
namespace Traffic\Repository;

use Traffic\Cache\Cache;
use Traffic\Cache\CacheService;
use Traffic\Cache\NoCache;
use Traffic\Logging\Service\LoggerService;
use Traffic\Settings\Repository\SettingsRepository;

class CachedSettingsRepository extends AbstractBaseRepository
{
    const CACHE_KEY = 'settings';

    private $_cachedSettings = [];

    public static function defaultCachePath()
    {
        return ROOT . Cache::DEFAULT_CACHE_DIR;
    }

    public static function defaultTestingCachePath()
    {
        return TESTS_ROOT . Cache::DEFAULT_CACHE_DIR;
    }

    public function getCache()
    {
        return CacheService::instance()->settingsCache();
    }

    public function setCachedSettings($settings)
    {
        $this->_cachedSettings = $settings;
    }

    public function cleanCache()
    {
        $this->_cachedSettings = null;
        $this->getCache()->delete(self::CACHE_KEY);
    }

    public function getCachedSettings()
    {
        return $this->_cachedSettings;
    }

    /**
     * @param $key bool
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->_cachedSettings);
    }

    /**
     * @param $key
     * @param null $default
     * @return string|int|null
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->_cachedSettings[$key];
        } elseif (isset($default)) {
            return $default;
        }

        return null;
    }

    public function loadFromCache()
    {
        try {
            $settings = $this->getCache()->get(self::CACHE_KEY);
        } catch (NoCache $e) {
            LoggerService::instance()->warning('Settings cache is empty! It must be reloaded (Maintenance > Status).');
            return [];
        }

        return $settings;
    }

    public function warmup()
    {
        $settings = SettingsRepository::instance()->allAsHash();
        $this->setCachedSettings($settings);
        CacheService::instance()->settingsCache()->set(self::CACHE_KEY, $settings);
    }
}