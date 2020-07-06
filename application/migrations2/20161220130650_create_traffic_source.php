<?php
class Migration_20161220130650_CreateTrafficSource extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы traffic_source';

    const DESCRIPTION_EN = 'Create table traffic_source';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}traffic_source (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `s2s_postback` TEXT NOT NULL,
            `template_name` varchar(255) DEFAULT NULL,
            `accept_tokens` tinyint(1) unsigned NOT NULL DEFAULT '0',
            `token` TEXT DEFAULT NULL,
            `state` varchar(255),
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        self::execute($sql);
    }
}