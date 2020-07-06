<?php
class Migration_20161223122446_CreateDestinations extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы ref_destinations';

    const DESCRIPTION_EN = 'Create table ref_destinations';

    public static function up()
    {
        $prefix = self::getPrefix();

        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}ref_destinations` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(255) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        self::execute($sql);
    }
}