<?php
use Traffic\Service\SettingsService;


class Migration_25 extends Migration {

    const DESCRIPTION_RU = 'Добавление geodb в настройки';

    const DESCRIPTION_EN = 'Add geodb to settings';

    public static function up()
    {
        if (file_exists(ROOT . '/var/geoip/GeoIPCity.dat')) {
            SettingsService::instance()->updateValue('geodb', 'maxmind_full');
        } else {
            SettingsService::instance()->updateValue('geodb', 'maxmind_lite');
        }
    }
}