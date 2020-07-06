<?php
class Migration_20161220121100_CreateVisitors extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы visitors';

    const DESCRIPTION_EN = 'Create table visitors';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE {$prefix}visitors (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            code varchar(100) NOT NULL,
            ip_id int(10) unsigned NOT NULL,
            user_agent_id int(10) unsigned NOT NULL,
            country_id int(10) unsigned NOT NULL,
            region_id int(10) unsigned NOT NULL,
            city_id int(10) unsigned NOT NULL,
            device_type_id int(10) unsigned NOT NULL,
            device_model_id int(10) unsigned NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY code (`code`),
            KEY user_agent_id (user_agent_id),
            KEY ip_id (ip_id),
            KEY country_id (country_id),
            KEY region_id (region_id),
            KEY city_id (city_id),
            KEY device_type_id (device_type_id),
            KEY device_model_id (device_model_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        self::execute($sql);
    }
}