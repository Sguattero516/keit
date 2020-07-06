<?php
class Migration_20161223113029_CreateRefSearchEngines extends Migration 
{
    const DESCRIPTION_RU = 'Создание ref_search_engines';

    const DESCRIPTION_EN = 'Create ref_search_engines';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}ref_search_engines (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        self::execute($sql);
    }
}