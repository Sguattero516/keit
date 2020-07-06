<?php
namespace Traffic\GeoDb\Service;

use Component\GeoDb\AbstractGeoDb;
use Component\GeoDb\Error\DbError;
use Component\GeoDb\Error\DbNotFound;
use Component\GeoDb\Error\DbUpdateError;
use Traffic\GeoDb\IpInfoType;
use Traffic\Logging\Service\LoggerService;
use Traffic\Model\Setting;
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Service\AbstractService;
use Traffic\Service\SettingsService;

class GeoDbService extends AbstractService
{
    /**
     * @param AbstractGeoDb $db
     * @throws DbUpdateError
     */
    public function update(AbstractGeoDb $db)
    {
        $db->manager()->update();
    }

    public function delete(AbstractGeoDb $db)
    {
        $db->manager()->delete();
    }

    /**
     * @param $dbs AbstractGeoDb[]
     * @throws
     */
    public function updateMany($dbs)
    {
        $errors = [];
        foreach ($dbs as $db) {
            if ($db->isUpdateAvailable()) {
                try {
                    $this->update($db);
                } catch (DbUpdateError $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        if (!empty($errors)) {
            throw new DbUpdateError(implode('; ', $errors));
        }
    }

    public function settings()
    {
        $value = CachedSettingsRepository::instance()->get(Setting::IPDB);
        if (!empty($value)) {
            return json_decode($value, true);
        }
        return [];
    }

    public function setDbForDataType($dataType, $dbId)
    {
        $settings = $this->settings();
        $settings[$dataType] = $dbId;
        SettingsService::instance()->updateValue(Setting::IPDB, json_encode($settings));
    }

    public function saveSettings($settings)
    {
        if (!is_array($settings)) {
            throw new \Exception("Trying to save incorrect settings: " . json_encode($settings));
        }
        SettingsService::instance()->updateValue(Setting::IPDB, json_encode($settings));
        return $this->settings();
    }

    /**
     * @param AbstractGeoDb $db
     * @param $ip
     * @return array
     * @throws DbError
     * @throws \Component\GeoDb\Error\DbNotFound
     */
    public function info(AbstractGeoDb $db, $ip)
    {
        if ($db->exists()) {
            return $db->adapter()->info($ip);
        } else {
            return [];
        }
    }

    public function rawInfo(AbstractGeoDb $db, $ip)
    {
        try {
            return $db->adapter()->rawInfo($ip);
        } catch (DbNotFound $exception) {
            LoggerService::instance()->error('IGNORED: ' . $exception->getMessage());
            return [];
        }
    }

    public function canDbResolveDataType($dataType, AbstractGeoDb $db, AbstractGeoDb $dbBySettings = null )
    {
        if (!in_array($dataType, $db->definition()->dataTypes())) {
            return false;
        }
        if (empty($dbBySettings)) {
            return true;
        }
        return empty($dbBySettings) || (get_class($db->adapter()) === get_class($dbBySettings->adapter()));
    }
}
