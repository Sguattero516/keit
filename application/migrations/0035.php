<?php
use Traffic\Service\SettingsService;


class Migration_35 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы keitaro_archive_device_types';

    const DESCRIPTION_EN = 'Create table keitaro_archive_device_types';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();

        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}archive_device_types` (
          `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
          `device_type` varchar(70) NOT NULL,
          `group_id` int(11) NOT NULL,
          `date` date NOT NULL,
          `count` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `date_group_value` (`date`,`group_id`,`device_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        self::execute($sql);

        SettingsService::instance()->updateValue('store_archive_device_types_period', 7);
        SettingsService::instance()->updateValue('device_types_report_enabled', 0);
    }
}