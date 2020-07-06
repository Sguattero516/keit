<?php
use Traffic\Service\SettingsService;


class Migration_34 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы keitaro_archive_os';

    const DESCRIPTION_EN = 'Create table keitaro_archive_os';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();

        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}archive_os` (
          `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
          `os` varchar(70) NOT NULL,
          `group_id` int(11) NOT NULL,
          `date` date NOT NULL,
          `count` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `date_group_value` (`date`,`group_id`,`os`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        self::execute($sql);

        SettingsService::instance()->updateValue('store_archive_os_period', 7);
        SettingsService::instance()->updateValue('os_report_enabled', 0);
    }
}