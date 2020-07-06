<?php
class Migration_20170501203702_AddDomain extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы domains';

    const DESCRIPTION_EN = 'Create table domains';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}domains (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `is_ssl` tinyint(1) NOT NULL,
            `network_status` varchar(255),
            `default_campaign_id` int(10) unsigned DEFAULT NULL,
            `state` varchar(255),
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB";

        self::execute($sql);
    }
}