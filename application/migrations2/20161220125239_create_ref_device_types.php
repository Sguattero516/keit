<?php
class Migration_20161220125239_CreateRefDeviceTypes extends Migration 
{
    const DESCRIPTION_RU = 'Создание ref_connection_types';

    const DESCRIPTION_EN = 'Create ref_connection_types';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}ref_device_types` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `value` varchar(15) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `value` (`value`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        self::execute($sql);
    }
}