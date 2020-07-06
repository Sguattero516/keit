<?php
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Model\Setting;
use Traffic\Service\SettingsService;
use Traffic\GeoDb\IpInfoType;
use Traffic\GeoDb\Repository\GeoDbRepository;
use Component\GeoDb\Ip2Location\Ip2LocationDb3Full;
use Component\GeoDb\Ip2Location\Ip2LocationDb3Lite;
use Component\GeoDb\Ip2Location\Ip2LocationDb4;
use Component\GeoDb\Ip2Location\Ip2LocationPx2;
use Component\GeoDb\Keitaro\KeitaroBotDb2;
use Component\GeoDb\Keitaro\KeitaroCarrierDb;
use Component\GeoDb\Maxmind\MaxmindCityFull;
use Component\GeoDb\Maxmind\MaxmindCityLite;
use Component\GeoDb\Maxmind\MaxmindConnectionType;
use Component\GeoDb\Maxmind\MaxmindCountryFull;
use Component\GeoDb\Maxmind\MaxmindIsp;
use Component\GeoDb\Sypex\SypexCityFull;
use Component\GeoDb\Sypex\SypexCityLite;
use Component\GeoDb\Sypex\SypexCityMax;

class Migration_20190622184545_UpdateGeodbSettings extends Migration
{
    const DESCRIPTION_RU = 'Обновление настроек для гео баз';

    const DESCRIPTION_EN = 'Update settings for geo db';

    public static function up()
    {
        $params = CachedSettingsRepository::instance()->get(Setting::IPDB);
        if (!empty($params)) {
            $currentSettings = json_decode($params, true);
        } else {
            $currentSettings = [];
        }

        $settings = self::updateSettings($currentSettings);
        if ($settings != $currentSettings) {
            SettingsService::instance()->updateValue(Setting::IPDB, json_encode($settings));
        }
    }

    public static function updateSettings($settings)
    {
        $oldSettings = self::buildFromOldSettings();
        if ($oldSettings && is_array($oldSettings)) {
            foreach($oldSettings as $key => $value) {
                if (!isset($settings[$key]) || empty($settings[$key])) {
                    $settings[$key] = $value;
                }
            }
        }
        return $settings;
    }

    public static function buildFromOldSettings()
    {
        $data = array_map(function(){
            return null;
        }, IpInfoType::all());

        $db = null;
        switch (CachedSettingsRepository::instance()->get('geodb')) {
            case 'sypex_lite':
                $db = GeoDbRepository::instance()->getDbInfo(SypexCityLite::ID);
                break;
            case 'sypex_full':
                $db = GeoDbRepository::instance()->getDbInfo(SypexCityFull::ID);
                break;
            case 'sypex_max':
                $db = GeoDbRepository::instance()->getDbInfo(SypexCityMax::ID);
                break;
            case 'maxmind_lite':
                $db = GeoDbRepository::instance()->getDbInfo(MaxmindCityLite::ID);
                break;
            case 'maxmind_full':
                $db = GeoDbRepository::instance()->getDbInfo(MaxmindCityFull::ID);
                break;
            case 'ip2location_lite':
                $db = GeoDbRepository::instance()->getDbInfo(Ip2LocationDb3Lite::ID);
                break;
            case 'ip2location_full':
                $db = GeoDbRepository::instance()->getDbInfo(Ip2LocationDb3Full::ID);
                break;
            case 'ip2location_full_isp':
                $db = GeoDbRepository::instance()->getDbInfo(Ip2LocationDb4::ID);
                break;
        };

        if ($db) {
            foreach ($db->definition()->dataTypes() as $dataType) {
                $data[$dataType] = $db->definition()->id();
            }
        }

        if (GeoDbRepository::instance()->isAvailable(MaxmindConnectionType::ID)) {
            $data[IpInfoType::CONNECTION_TYPE] = MaxmindConnectionType::ID;
        } else {
            $data[IpInfoType::CONNECTION_TYPE] = KeitaroCarrierDb::ID;
        }

        if (strstr(CachedSettingsRepository::instance()->get('geodb'), 'lite')
            && GeoDbRepository::instance()->isAvailable(MaxmindCountryFull::ID)) {
            $data[IpInfoType::COUNTRY] = MaxmindCountryFull::ID;
        }

        $data[IpInfoType::OPERATOR] = KeitaroCarrierDb::ID;
        $data[IpInfoType::BOT_TYPE] = KeitaroBotDb2::ID;

        if (GeoDbRepository::instance()->isAvailable(MaxmindIsp::ID)) {
            $data[IpInfoType::ISP] = MaxmindIsp::ID;
        }

        if (GeoDbRepository::instance()->isAvailable(Ip2LocationDb4::ID)) {
            $data[IpInfoType::ISP] = Ip2LocationDb4::ID;
        }

        if (GeoDbRepository::instance()->isAvailable(Ip2LocationPx2::ID)) {
            $data[IpInfoType::PROXY_TYPE] = Ip2LocationPx2::ID;
        }
        return $data;
    }
}