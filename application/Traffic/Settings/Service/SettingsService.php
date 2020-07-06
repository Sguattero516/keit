<?php
namespace Traffic\Service;

use Core\Application\Bootstrap;
use Core\Db\DataService;
use Core\Entity\Service\EntityService;
use Traffic\CachedData\Repository\CachedDataRepository;
use Traffic\Model\Setting;
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Settings\Repository\SettingsRepository;

class SettingsService extends EntityService
{
    const FOLDER_FILTER = '/[^a-z^0-9^-\_]/si';

    private $_ignoreKeys = ['correction_time'];

    public function definition()
    {
        return Setting::definition();
    }

    public function updateValue($key, $value)
    {
        $settings = [$key => $value];
        $this->updateValues($settings);
    }

    public function updateValues(array $newSettings)
    {
        $newSettings = $this->_filterMany($newSettings);
        $oldSettings = SettingsRepository::instance()->allAsHash();

        $cacheStorageChanged = (isset($newSettings[Setting::CACHE_STORAGE]) && isset($oldSettings[Setting::CACHE_STORAGE])
            && $newSettings[Setting::CACHE_STORAGE] != $oldSettings[Setting::CACHE_STORAGE]);

        foreach ($newSettings as $key => $value) {
            if (!in_array($key, $this->_ignoreKeys)) {
                $this->_updateInDb($key, $value);
            }
        }

        CachedSettingsRepository::instance()->warmup();

        if ($cacheStorageChanged) {
            Bootstrap::initCacheService();
            Bootstrap::initEntityCache();
            CachedDataRepository::instance()->warmup();
        }
    }

    public function refreshCache()
    {
        CachedSettingsRepository::instance()->cleanCache();
        CachedSettingsRepository::instance()->warmup();
    }


    /* @TODO создать миграцию и удалить */
    public function checkDefaults()
    {
        $apiKey = CachedSettingsRepository::instance()->get('api_key');
        if (empty($apiKey)) {
            $key = $this->_getNewApiKey();
            $this->updateValue('api_key', $key);
        }
    }

    private function _getNewApiKey()
    {
        return md5(rand(10000, 999999) . SALT);
    }

    public function resetDefaultActionOnCampaignDisable($campaignId)
    {
        $settingsRepository = SettingsRepository::instance();
        $extraActionSetting = $settingsRepository->findByKey(Setting::EXTRA_ACTION);
        $extraCampaignSetting = $settingsRepository->findByKey(Setting::EXTRA_CAMPAIGN);

        if ($extraActionSetting && $extraActionSetting->getValue() === Setting::EXTRA_ACTION_PARAM_CAMPAIGN &&
            $extraCampaignSetting && $extraCampaignSetting->getValue() == $campaignId) {
            $this->updateValues([Setting::EXTRA_ACTION => Setting::EXTRA_ACTION_PARAM_NOT_FOUND]);
            DataService::instance()->delete(Setting::definition(), $extraCampaignSetting);
        }
    }

    private function _updateInDb($key, $value)
    {
        $setting = SettingsRepository::instance()->findByKey($key);
        if (empty($setting)) {
            $this->create([
                'key' => $key,
                'value' => $value
            ]);
        } else {
            $this->update($setting, ['value' => $value]);
        }
    }

    private function _filterMany($items)
    {
        foreach ($items as $key => $value) {
            $items[$key] = $this->_filter($key, $value);
        }
        return $items;
    }

    private function _filter($key, $value)
    {
        switch ($key) {
            case Setting::LP_DIR:
                $value = preg_replace(self::FOLDER_FILTER, '', $value);
                break;
        }
        return $value;
    }
}
