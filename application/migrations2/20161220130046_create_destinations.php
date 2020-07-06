<?php
class Migration_20161220130046_CreateDestinations extends Migration 
{
    const DESCRIPTION_RU = 'Создание click_destinations';

    const DESCRIPTION_EN = 'Create click_destinations';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}click_destinations` (
              `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
              `destination` varchar(15) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `destination` (`destination`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        self::execute($sql);
    }
}